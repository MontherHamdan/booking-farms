<div class="row">
    <!-- Order Status Card -->
    <div class="col-xl-4 d-flex">
        <div class="card shadow-lg border-0 w-100">
            <div class="card-body d-flex flex-column align-items-center justify-content-center h-100">
                <h4 class="header-title mt-0 d-flex align-items-center justify-content-between w-100">
                    Order Status
                    <span class="badge bg-primary p-2 fs-6">
                        Total: {{ $totalOrders }}
                    </span>
                </h4>
                <!-- Wrap canvas in a container with fixed size -->
                <div style="width: 330px; height: 330px;">
                    <canvas id="orderStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <!-- end col -->

    <!-- Orders With/Without Additives Chart - Enhanced -->
    <div class="col-xl-4 col-md-6">
        <div class="card shadow-lg border-0 w-100">
            <div class="card-body">
                <h4 class="header-title mt-0">Orders With & Without Additives</h4>
                <!-- Use a fixed-height container for better responsiveness -->
                <div class="chart-container" style="position: relative; height: 400px; width: 100%;">
                    <canvas id="additivesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Selling Products Chart - Vertical Bar Chart -->
    <div class="col-xl-4 d-flex">
        <div class="card shadow-lg border-0 w-100">
            <div class="card-body d-flex flex-column align-items-center justify-content-center" style="height: 300px;">
                <h4 class="header-title mt-0">Top Selling Products</h4>
                <div style="position: relative; height: calc(100% - 40px); width: 100%;">
                    <canvas id="topSellingChart"></canvas>
                </div>
            </div>
        </div>
    </div>


</div>