@extends('layout')
@section('content')
    <h3 class="mb-3">Dashboard</h3>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body kpi-card">
                    <div class="kpi-icon" style="background: var(--mosque-blue);">&#128101;</div>
                    <div>
                        <div class="kpi-value">{{ $totalStudents }}</div>
                        <div class="kpi-label">Total Students</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body kpi-card">
                    <div class="kpi-icon" style="background: var(--mosque-green);">&#10003;</div>
                    <div>
                        <div class="kpi-value">{{ $presentToday }}</div>
                        <div class="kpi-label">Present Today</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body kpi-card">
                    <div class="kpi-icon" style="background: #b45309;">&#10005;</div>
                    <div>
                        <div class="kpi-value">{{ $absentToday }}</div>
                        <div class="kpi-label">Absent Today</div>
                    </div>
                </div>
            </div>
        </div>
        @if ($paymentsThisMonth)
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body kpi-card">
                        <div class="kpi-icon" style="background: var(--mosque-blue-dark);">&#128176;</div>
                        <div>
                            <div class="kpi-value">{{ $paymentsThisMonth['paid'] }} / {{ $paymentsThisMonth['pending'] }}</div>
                            <div class="kpi-label">Fees Paid / Pending (this month)</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="card chart-card h-100">
                <div class="card-body">
                    <h5 class="card-title">Attendance — Last 7 Days</h5>
                    <canvas id="attendanceTrendChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Recent Activity</h5>
                    @forelse ($recentActivity as $record)
                        <div class="activity-item">
                            <span>{{ $record->student->first_name }} {{ $record->student->last_name }} checked into {{ $record->subject->name }}</span>
                            <span class="text-muted small">{{ $record->created_at?->format('h:i A') ?? $record->date }}</span>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No attendance recorded yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        new Chart(document.getElementById('attendanceTrendChart'), {
            type: 'line',
            data: {
                labels: @json(collect($trend)->pluck('label')),
                datasets: [{
                    label: 'Students present',
                    data: @json(collect($trend)->pluck('count')),
                    borderColor: '#1f8f5f',
                    backgroundColor: 'rgba(31, 143, 95, 0.15)',
                    tension: 0.35,
                    fill: true,
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    </script>
@endsection
