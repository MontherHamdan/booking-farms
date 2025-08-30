<?php

namespace App\Http\Controllers\Api\FarmOwner;

use App\Http\Controllers\Controller;
use App\Http\Requests\FarmOwner\StoreFarmOwnerBankAccountRequest;
use App\Models\FarmOwnerBankAccount;
use App\Traits\JsonResponseTrait;
use App\Traits\ExceptionLoggerTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class ApiFarmOwnerBankAccountController extends Controller
{
    use JsonResponseTrait, ExceptionLoggerTrait;

    /**
     * Get farm owner's bank account
     */
    public function show(): JsonResponse
    {
        try {
            $user = Auth::user();
            $bankAccount = $user->farmOwnerBankAccount;

            if (!$bankAccount) {
                return $this->successResponse(true, null, __('bank_account.messages.not_found'), 200);
            }

            $responseData = [
                'id' => $bankAccount->id,
                'account_type' => $bankAccount->account_type,
                'account_type_label' => __('bank_account.account_types.' . $bankAccount->account_type),
                'account_holder_name' => $bankAccount->account_holder_name,
                'primary_identifier' => $bankAccount->primary_identifier,
                'formatted_details' => $bankAccount->formatted_account_details,
                'is_active' => $bankAccount->is_active,
                'created_at' => $bankAccount->created_at,
                'updated_at' => $bankAccount->updated_at,
            ];
            
            return $this->successResponse(true, $responseData, null, 200);

        } catch (Exception $e) {
            $this->logException($e, [
                'action' => 'get bank account',
                'user_id' => Auth::id()
            ]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Save/Update bank account
     */
    public function store(StoreFarmOwnerBankAccountRequest $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $user = Auth::user();

            DB::beginTransaction();

            // Prepare data for saving
            $bankData = [
                'user_id' => $userId,
                'account_type' => $request->account_type,
                'account_holder_name' => $request->account_holder_name,
                'is_active' => true,
            ];

            // Add type-specific fields
            if ($request->account_type === FarmOwnerBankAccount::TYPE_IBAN) {
                $bankData['iban'] = $request->iban;
                $bankData['bank_name'] = $request->bank_name;
                $bankData['cliq_alias'] = null;
                $bankData['cliq_phone'] = null;
            } elseif ($request->account_type === FarmOwnerBankAccount::TYPE_CLIQ) {
                $bankData['cliq_alias'] = $request->cliq_alias;
                $bankData['cliq_phone'] = $request->cliq_phone;
                $bankData['iban'] = null;
                $bankData['bank_name'] = null;
            }

            // Create or update bank account
            $bankAccount = FarmOwnerBankAccount::updateOrCreate(
                ['user_id' => $userId],
                $bankData
            );

            DB::commit();

            $responseData = [
                'id' => $bankAccount->id,
                'account_type' => $bankAccount->account_type,
                'account_type_label' => __('bank_account.account_types.' . $bankAccount->account_type),
                'account_holder_name' => $bankAccount->account_holder_name,
                'primary_identifier' => $bankAccount->primary_identifier,
                'formatted_details' => $bankAccount->formatted_account_details,
                'is_active' => $bankAccount->is_active,
                'created_at' => $bankAccount->created_at,
                'updated_at' => $bankAccount->updated_at,
            ];

            return $this->successResponse(true, [
                'message' => __('bank_account.messages.saved_successfully'),
                'data'    => $responseData, 
            ], null, 200);

        } catch (Exception $e) {
            DB::rollBack();
            $this->logException($e, [
                'action' => 'save bank account',
                'user_id' => Auth::id(),
                'account_type' => $request->account_type ?? null
            ]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Delete bank account
     */
    public function destroy(): JsonResponse
    {
        try {
            $user = Auth::user();
            $bankAccount = $user->farmOwnerBankAccount;

            if (!$bankAccount) {
                return $this->errorResponse(__('bank_account.messages.no_account_to_delete'), 404);
            }

            $bankAccount->delete();

            return $this->successResponse(true, null, __('bank_account.messages.deleted_successfully'), 200);

        } catch (Exception $e) {
            $this->logException($e, [
                'action' => 'delete bank account',
                'user_id' => Auth::id()
            ]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Get available account types and their validation requirements
     */
    public function accountTypes(): JsonResponse
    {
        try {
            $accountTypes = [
                'iban' => [
                    'key' => FarmOwnerBankAccount::TYPE_IBAN,
                    'label' => __('bank_account.account_types.iban'),
                    'required_fields' => ['account_holder_name', 'iban', 'bank_name'],
                    'description' => 'Traditional bank account using IBAN',
                    'validation_hints' => [
                        'iban' => 'Format: XX00XXXX0000000000000 (Country code + check digits + account identifier)',
                        'bank_name' => 'Full name of your bank',
                    ]
                ],
                'cliq' => [
                    'key' => FarmOwnerBankAccount::TYPE_CLIQ,
                    'label' => __('bank_account.account_types.cliq'),
                    'required_fields' => ['account_holder_name', 'cliq_alias_or_phone'],
                    'description' => 'Instant transfer using CLIQ alias or phone number',
                    'validation_hints' => [
                        'cliq_alias' => 'Your CLIQ alias (text identifier)',
                        'cliq_phone' => 'Phone number registered with CLIQ',
                        'note' => 'You need either alias OR phone number (or both)',
                    ]
                ]
            ];

            return $this->successResponse(true, $accountTypes, null, 200);

        } catch (Exception $e) {
            $this->logException($e, [
                'action' => 'get account types',
                'user_id' => Auth::id()
            ]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }
}