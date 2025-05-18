<div class="row flex-nowrap mt-2">
    <!-- Pending Order Card -->
    <div class="col-md-2 col-sm-6 col-12">
      <div class="card card-enhanced">
        <div class="card-body d-flex align-items-center p-3">
          <div class="icon-circle bg-warning text-white me-3">
            <i class="mdi mdi-clock-outline" style="font-size:20px;"></i>
          </div>
          <div>
            <div class="card-title">Pending</div>
            <div class="card-count">{{ $pendingCount }}</div>
          </div>
        </div>
      </div>
    </div>
  
    <!-- Preparing Order Card -->
    <div class="col-md-2 col-sm-6 col-12">
      <div class="card card-enhanced">
        <div class="card-body d-flex align-items-center p-3">
          <div class="icon-circle bg-purple text-white me-3">
            <i class="mdi mdi-cog-outline" style="font-size:20px;"></i>
          </div>
          <div>
            <div class="card-title">Preparing</div>
            <div class="card-count">{{ $preparingCount }}</div>
          </div>
        </div>
      </div>
    </div>
  
    <!-- Completed Order Card -->
    <div class="col-md-2 col-sm-6 col-12">
      <div class="card card-enhanced">
        <div class="card-body d-flex align-items-center p-3">
          <div class="icon-circle bg-success text-white me-3">
            <i class="mdi mdi-check-circle-outline" style="font-size:20px;"></i>
          </div>
          <div>
            <div class="card-title">Completed</div>
            <div class="card-count">{{ $completedCount }}</div>
          </div>
        </div>
      </div>
    </div>
  
    <!-- Out for Delivery Card -->
    <div class="col-md-2 col-sm-6 col-12">
      <div class="card card-enhanced">
        <div class="card-body d-flex align-items-center p-3">
          <div class="icon-circle bg-pink text-white me-3">
            <i class="mdi mdi-truck-delivery-outline" style="font-size:20px;"></i>
          </div>
          <div>
            <div class="card-title">Delivery</div>
            <div class="card-count">{{ $outForDeliveryCount }}</div>
          </div>
        </div>
      </div>
    </div>
  
    <!-- Received Order Card -->
    <div class="col-md-2 col-sm-6 col-12">
      <div class="card card-enhanced">
        <div class="card-body d-flex align-items-center p-3">
          <div class="icon-circle bg-primary text-white me-3">
            <i class="mdi mdi-package-variant-closed" style="font-size:20px;"></i>
          </div>
          <div>
            <div class="card-title">Received</div>
            <div class="card-count">{{ $receivedCount }}</div>
          </div>
        </div>
      </div>
    </div>
  
    <!-- Canceled Order Card -->
    <div class="col-md-2 col-sm-6 col-12">
      <div class="card card-enhanced">
        <div class="card-body d-flex align-items-center p-3">
          <div class="icon-circle bg-danger text-white me-3">
            <i class="mdi mdi-close-circle-outline" style="font-size:20px;"></i>
          </div>
          <div>
            <div class="card-title">Canceled</div>
            <div class="card-count">{{ $canceledCount }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
  