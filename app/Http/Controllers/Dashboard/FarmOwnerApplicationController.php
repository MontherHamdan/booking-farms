<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\FarmOwnerApplication;
use App\Models\User;
use App\Services\FarmOwnerApplicationService;
use App\Traits\LogErrorAndRedirectTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FarmOwnerApplicationController extends Controller
{
    use LogErrorAndRedirectTrait;

    protected $applicationService;

    public function __construct(FarmOwnerApplicationService $applicationService)
    {
        $this->applicationService = $applicationService;
    }

    /**
     * Display a listing of farm owner applications.
     */
    public function index()
    {
        try {
            $query = FarmOwnerApplication::with(['user.city']);

            // Search functionality
            if (request('search')) {
                $search = request('search');
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%")
                      ->orWhere('phone', 'LIKE', "%{$search}%");
                });
            }

            // Verification status filter
            if (request('status_filter')) {
                $query->where('id_verification_status', request('status_filter'));
            }

            // Sorting
            $sortBy = request('sort', 'applied_at');
            $direction = request('direction', 'desc');

            switch ($sortBy) {
                case 'user_name':
                    $query->join('users', 'farm_owner_applications.user_id', '=', 'users.id')
                          ->orderBy('users.name', $direction)
                          ->select('farm_owner_applications.*');
                    break;
                case 'applied_at':
                case 'verified_at':
                case 'id_verification_status':
                    $query->orderBy($sortBy, $direction);
                    break;
                default:
                    $query->orderBy('applied_at', 'desc');
            }

            $applications = $query->paginate(15);

            // Get statistics
            $stats = [
                'total' => FarmOwnerApplication::count(),
                'pending' => FarmOwnerApplication::pending()->count(),
                'verified' => FarmOwnerApplication::verified()->count(),
                'without_id' => FarmOwnerApplication::whereNull('id_image')->count(),
            ];

            return view('admin.farm-owner-applications.index', compact('applications', 'stats'));
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error in farm owner applications page: ');
            return abort(500);
        }
    }

    /**
     * Display the specified application.
     */
    public function show($id)
    {
        try {
            $application = FarmOwnerApplication::with(['user.city', 'user.ownedFarms', 'user.farmOwnerBankAccount.bank'])
                ->findOrFail($id);

            return view('admin.farm-owner-applications.show', compact('application'));
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error viewing application: ');
            return redirect()->route('dashboard.farm-owner-applications.index')
                ->with('error', 'Application not found.');
        }
    }

    /**
     * Verify ID image.
     */
    public function verify($id)
    {
        try {
            DB::beginTransaction();

            $application = FarmOwnerApplication::findOrFail($id);

            if (!$application->hasIdImage()) {
                return redirect()->back()
                    ->with('error', 'No ID image to verify.');
            }

            if ($application->isVerified()) {
                return redirect()->back()
                    ->with('warning', 'ID image is already verified.');
            }

            // Verify using service
            $this->applicationService->verifyIdImage($application->id);

            DB::commit();

            return redirect()->back()
                ->with('success', 'ID image verified successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logErrorAndRedirect($e, 'Error verifying ID image: ');

            return redirect()->back()
                ->with('error', 'Internal server error. Please try again later.');
        }
    }
}