<!-- Commission Rate Modal -->
<div class="modal fade" id="commissionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('dashboard.wallet.wallets.commission-rate', $wallet->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Update Commission Rate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="mdi mdi-information me-2"></i>
                        Updating commission rate for <strong>{{ $wallet->user->name }}</strong>
                        <br>Current rate: <strong>{{ $wallet->platform_commission_rate }}%</strong>
                    </div>
                    
                    @php
                        $minRate = \App\Models\PlatformSetting::getMinimumCommissionRate();
                        $maxRate = \App\Models\PlatformSetting::getMaximumCommissionRate();
                        $defaultRate = \App\Models\PlatformSetting::getDefaultCommissionRate();
                    @endphp
                    
                    <div class="mb-3">
                        <label for="commissionRate" class="form-label">Commission Rate (%) *</label>
                        <input type="number" class="form-control" id="commissionRate" name="commission_rate" 
                               step="0.01" min="{{ $minRate }}" max="{{ $maxRate }}" 
                               value="{{ $wallet->platform_commission_rate }}" required>
                        <div class="form-text">
                            Rate must be between {{ $minRate }}% and {{ $maxRate }}%
                            @if($defaultRate != $wallet->platform_commission_rate)
                                <br><small class="text-muted">Platform default: {{ $defaultRate }}%</small>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Quick Rate Buttons -->
                    @if($minRate != $maxRate)
                    <div class="mb-3">
                        <label class="form-label">Quick Set:</label>
                        <div class="d-flex gap-2 flex-wrap">
                            @if($minRate > 0)
                            <button type="button" class="btn btn-sm btn-outline-secondary quick-rate-btn" data-rate="{{ $minRate }}">
                                {{ $minRate }}% (Min)
                            </button>
                            @endif
                            
                            @if($defaultRate != $minRate && $defaultRate != $maxRate)
                            <button type="button" class="btn btn-sm btn-outline-primary quick-rate-btn" data-rate="{{ $defaultRate }}">
                                {{ $defaultRate }}% (Default)
                            </button>
                            @endif
                            
                            @if($maxRate < 100)
                            <button type="button" class="btn btn-sm btn-outline-secondary quick-rate-btn" data-rate="{{ $maxRate }}">
                                {{ $maxRate }}% (Max)
                            </button>
                            @endif
                        </div>
                    </div>
                    @endif
                    
                    <div class="mb-3">
                        <label for="commissionReason" class="form-label">Reason for Change</label>
                        <textarea class="form-control" id="commissionReason" name="reason" rows="3" 
                                  placeholder="Optional reason for this change..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-percent me-1"></i> Update Commission
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>