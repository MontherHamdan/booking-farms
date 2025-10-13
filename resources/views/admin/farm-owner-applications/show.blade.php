@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h4 class="font-weight-bold text-primary">
                <i class="mdi mdi-account-details mr-2"></i>Farm Owner Application Details
            </h4>
        </div>
        <div class="col-auto">
            <a href="{{ route('dashboard.farm-owner-applications.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to Applications
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: User & Application Info -->
        <div class="col-md-8">
            <!-- Farm Owner Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header text-white">
                    <h5 class="mb-0"><i class="mdi mdi-account mr-2"></i>Farm Owner Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 text-center">
                            @if($application->user->avatar)
                                <img src="{{ $application->user->avatar }}" 
                                     alt="{{ $application->user->name }}" 
                                     class="rounded-circle img-thumbnail" 
                                     width="80" height="80">
                            @else
                                @php
                                    $nameParts = explode(' ', $application->user->name);
                                    $initials = collect($nameParts)
                                        ->filter(fn($part) => strlen($part) > 0)
                                        ->take(2)
                                        ->map(fn($part) => strtoupper(substr($part, 0, 1)))
                                        ->implode('');
                                @endphp
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-secondary text-white" 
                                     style="width: 80px; height: 80px; font-size: 1.5rem;">
                                    {{ $initials }}
                                </div>
                            @endif
                        </div>
                        <div class="col-md-10">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <th width="200">Name:</th>
                                    <td>{{ $application->user->name }}</td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $application->user->email ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Phone:</th>
                                    <td>{{ $application->user->phone }}</td>
                                </tr>
                                <tr>
                                    <th>City:</th>
                                    <td>
                                        @if($application->user->city)
                                            {{ $application->user->city->name_en }} ({{ $application->user->city->name_ar }})
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>User ID:</th>
                                    <td><span class="badge bg-secondary">#{{ $application->user->id }}</span></td>
                                </tr>
                                <tr>
                                    <th>Registered:</th>
                                    <td>{{ $application->user->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ID Image Section -->
            <div class="card shadow-sm mb-4">
                <div class="card-header  text-white">
                    <h5 class="mb-0"><i class="mdi mdi-card-account-details mr-2"></i>ID Image Verification</h5>
                </div>
                <div class="card-body">
                    @if($application->hasIdImage())
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="font-weight-bold mb-3">ID Image Preview</h6>
                                {{-- Changed: Use S3 URL directly --}}
                                <img src="{{ $application->id_image }}" 
                                     alt="ID Image" 
                                     class="img-fluid img-thumbnail mb-2"
                                     style="max-height: 400px; object-fit: contain;"
                                     onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22300%22%3E%3Crect fill=%22%23ddd%22 width=%22400%22 height=%22300%22/%3E%3Ctext fill=%22%23999%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dominant-baseline=%22middle%22%3EImage not available%3C/text%3E%3C/svg%3E';">
                                <div class="text-center">
                                    {{-- Changed: Use S3 URL directly --}}
                                    <a href="{{ $application->id_image }}" 
                                       target="_blank" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="mdi mdi-open-in-new mr-1"></i>Open Full Size
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="font-weight-bold mb-3">Verification Details</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            @if($application->isVerified())
                                                <span class="badge bg-success">
                                                    <i class="mdi mdi-check-circle mr-1"></i>Verified
                                                </span>
                                            @else
                                                <span class="badge bg-warning">
                                                    <i class="mdi mdi-clock-outline mr-1"></i>Pending Verification
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Uploaded:</th>
                                        <td>{{ $application->applied_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Time Since Upload:</th>
                                        <td>{{ $application->applied_at->diffForHumans() }}</td>
                                    </tr>
                                    @if($application->verified_at)
                                    <tr>
                                        <th>Verified On:</th>
                                        <td>{{ $application->verified_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                    @endif
                                </table>

                                @if(!$application->isVerified())
                                    <div class="mt-4">
                                        <form action="{{ route('dashboard.farm-owner-applications.verify', $application->id) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Are you sure you want to verify this ID image?');">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-block">
                                                <i class="mdi mdi-check-circle mr-2"></i>Verify ID Image
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="mdi mdi-image-off mdi-48px mb-2"></i>
                            <p>No ID image uploaded yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Bank Account Information -->
            @if($application->user->farmOwnerBankAccount)
            <div class="card shadow-sm mb-4">
                <div class="card-header text-white">
                    <h5 class="mb-0"><i class="mdi mdi-bank mr-2"></i>Bank Account Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="200">Account Type:</th>
                            <td>
                                <span class="badge bg-primary">
                                    {{ $application->user->farmOwnerBankAccount->getAccountTypeLabel() }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Account Holder:</th>
                            <td>{{ $application->user->farmOwnerBankAccount->account_holder_name }}</td>
                        </tr>
                        <tr>
                            <th>Bank:</th>
                            <td>
                                @if($application->user->farmOwnerBankAccount->bank)
                                    {{ $application->user->farmOwnerBankAccount->bank->name_en }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @if($application->user->farmOwnerBankAccount->isIbanAccount())
                        <tr>
                            <th>IBAN:</th>
                            <td><code>{{ $application->user->farmOwnerBankAccount->iban }}</code></td>
                        </tr>
                        @elseif($application->user->farmOwnerBankAccount->isCliqAccount())
                        <tr>
                            <th>CLIQ Alias:</th>
                            <td>{{ $application->user->farmOwnerBankAccount->cliq_alias ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th>CLIQ Phone:</th>
                            <td>{{ $application->user->farmOwnerBankAccount->cliq_phone ?: '-' }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
            @else
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="mdi mdi-bank mr-2"></i>Bank Account Information</h5>
                </div>
                <div class="card-body text-center text-muted">
                    <i class="mdi mdi-bank-off mdi-48px mb-2"></i>
                    <p>No bank account added yet.</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column: Stats & Activity -->
        <div class="col-md-4">
            <!-- Quick Stats -->
            <div class="card shadow-sm mb-4">
                <div class="card-header text-white">
                    <h5 class="mb-0"><i class="mdi mdi-chart-line mr-2"></i>Farm Owner Stats</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <th><i class="mdi mdi-home-variant text-primary mr-2"></i>Total Farms:</th>
                            <td class="text-end">
                                <span class="badge bg-primary">{{ $application->user->ownedFarms->count() }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th><i class="mdi mdi-check-circle text-success mr-2"></i>Active Farms:</th>
                            <td class="text-end">
                                <span class="badge bg-success">
                                    {{ $application->user->ownedFarms->where('status', 'active')->count() }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th><i class="mdi mdi-clock-outline text-warning mr-2"></i>Pending Farms:</th>
                            <td class="text-end">
                                <span class="badge bg-warning">
                                    {{ $application->user->ownedFarms->where('status', 'pending')->count() }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Application Timeline -->
            <div class="card shadow-sm mb-4">
                <div class="card-header text-white">
                    <h5 class="mb-0"><i class="mdi mdi-timeline mr-2"></i>Application Timeline</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <i class="mdi mdi-check-circle text-success mr-2"></i>
                            <strong>Became Farm Owner</strong>
                            <br>
                            <small class="text-muted ml-4">{{ $application->applied_at->format('Y-m-d H:i') }}</small>
                        </li>
                        @if($application->hasIdImage())
                        <li class="mb-3">
                            <i class="mdi mdi-image text-info mr-2"></i>
                            <strong>ID Image Uploaded</strong>
                            <br>
                            <small class="text-muted ml-4">{{ $application->applied_at->format('Y-m-d H:i') }}</small>
                        </li>
                        @endif
                        @if($application->isVerified())
                        <li class="mb-3">
                            <i class="mdi mdi-shield-check text-success mr-2"></i>
                            <strong>ID Verified</strong>
                            <br>
                            <small class="text-muted ml-4">{{ $application->verified_at->format('Y-m-d H:i') }}</small>
                        </li>
                        @endif
                        @if($application->user->farmOwnerBankAccount)
                        <li class="mb-3">
                            <i class="mdi mdi-bank text-success mr-2"></i>
                            <strong>Bank Account Added</strong>
                            <br>
                            <small class="text-muted ml-4">{{ $application->user->farmOwnerBankAccount->created_at->format('Y-m-d H:i') }}</small>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow-sm">
                <div class="card-header text-white">
                    <h5 class="mb-0"><i class="mdi mdi-lightning-bolt mr-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    @if(!$application->isVerified() && $application->hasIdImage())
                        <form action="{{ route('dashboard.farm-owner-applications.verify', $application->id) }}" 
                              method="POST" 
                              onsubmit="return confirm('Are you sure you want to verify this ID image?');">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block mb-2">
                                <i class="mdi mdi-check-circle mr-2"></i>Verify ID Image
                            </button>
                        </form>
                    @endif
                    
                    @if($application->user->ownedFarms->count() > 0)
                        <a href="{{ route('dashboard.farms.index', ['owner_id' => $application->user->id]) }}" 
                           class="btn btn-outline-primary btn-block mb-2">
                            <i class="mdi mdi-home-variant mr-2"></i>View Farms
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection