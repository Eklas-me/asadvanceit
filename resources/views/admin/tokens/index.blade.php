@extends('layouts.dashboard')

@section('content')
    <div class="page-header fade-in">
        <h1 class="page-title">All Tokens</h1>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="aero-card">
                <form id="tokensFilterForm" method="GET" action="{{ route('admin.tokens.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Select Worker</label>
                            <select name="user_id" class="aero-select">
                                <option value="">Show All</option>
                                @foreach($workers as $worker)
                                    <option value="{{ $worker->id }}" {{ $request->user_id == $worker->id ? 'selected' : '' }}>
                                        {{ $worker->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">From</label>
                            <input type="datetime-local" name="from_datetime" class="aero-input" value="{{ $from }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">To</label>
                            <input type="datetime-local" name="to_datetime" class="aero-input" value="{{ $to }}">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" id="btnFilter" class="aero-btn aero-btn-primary me-2">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <button type="submit" id="btnExport" name="export" value="1" class="aero-btn"
                                style="background: var(--accent-aqua); color: white; border: none;">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            @if($paginatedUsers->isNotEmpty())
                <div class="aero-alert aero-alert-info text-center mb-3">
                    Total Tokens Found: <strong>{{ number_format($totalTokensCount) }}</strong>
                </div>

                <div class="row g-3" id="user-list-container">
                    @include('admin.tokens.list')
                </div>

                <!-- Shimmer Loading Skeleton -->
                <div id="shimmer-loader" style="display: none; margin-top: 20px;">
                    <div class="row g-3">
                        @for($i = 0; $i < 2; $i++)
                        <div class="col-md-6">
                            <div class="aero-card" style="position: relative; overflow: hidden;">
                                <!-- Shimmer Effect Overlay -->
                                <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
                                            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.05), transparent);
                                            animation: shimmer 1.5s infinite;"></div>
                                
                                <div class="aero-card-header d-flex justify-content-between align-items-center mb-3">
                                    <div style="width: 150px; height: 24px; background: rgba(255,255,255,0.1); border-radius: 4px;"></div>
                                    <div style="width: 80px; height: 30px; background: rgba(255,255,255,0.1); border-radius: 4px;"></div>
                                </div>
                                <div>
                                    @for($j = 0; $j < 3; $j++)
                                    <div style="margin-bottom: 10px; padding: 10px 0; border-bottom: 1px solid var(--border-light);">
                                        <div style="width: 100%; height: 16px; background: rgba(255,255,255,0.05); border-radius: 4px; mb-1"></div>
                                    </div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                        @endfor
                    </div>
                </div>

                <!-- Scroll Sentinel -->
                <div id="scroll-sentinel" style="height: 20px; text-align: center; color: var(--text-muted); padding: 20px;">
                    @if($paginatedUsers->hasMorePages())
                        <!-- Sentinel is active only if more pages exist initially -->
                    @else
                        End of results
                    @endif
                </div>

                <style>
                    @keyframes shimmer {
                        0% { transform: translateX(-100%); }
                        100% { transform: translateX(100%); }
                    }
                </style>
            @else
                <div class="aero-alert aero-alert-danger text-center">No tokens found for this range.</div>
            @endif
        </div>
    </div>
    <!-- Export Progress Modal -->
    <div id="exportModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; backdrop-filter: blur(5px);">
        <div
            style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 400px; background: #1a1a1a; padding: 30px; border-radius: 15px; border: 1px solid var(--border-light); text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
            <h3 style="color: white; margin-bottom: 20px;">Exporting Tokens...</h3>

            <div
                style="width: 100%; height: 10px; background: #333; border-radius: 5px; overflow: hidden; margin-bottom: 15px;">
                <div id="exportProgressBar"
                    style="width: 0%; height: 100%; background: var(--accent-aqua); transition: width 0.1s linear;"></div>
            </div>

            <div style="display: flex; justify-content: space-between; color: var(--text-muted); font-size: 14px;">
                <span id="exportCount">0 exported</span>
                <span id="exportPercent">0%</span>
            </div>

            <button id="closeExportModal" onclick="document.getElementById('exportModal').style.display='none'"
                style="display: none; margin-top: 20px;" class="aero-btn aero-btn-primary">Close</button>
        </div>
    </div>

    <!-- Search Loading Modal -->
    <div id="searchModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; backdrop-filter: blur(5px);">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
            <div class="spinner-border text-info" style="width: 3rem; height: 3rem; border-width: 4px;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h3 style="color: white; margin-top: 20px; text-shadow: 0 2px 10px rgba(0,0,0,0.5);">Searching Database...</h3>
            <p style="color: rgba(255,255,255,0.7); font-size: 14px;">This process runs securely on the optimized optimized
                engine.</p>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.btn-copy').forEach(button => {
            button.addEventListener('click', function () {
                const tokens = this.getAttribute('data-tokens');
                navigator.clipboard.writeText(tokens).then(() => {
                    alert('Tokens copied to clipboard!');
                });
            });
        });

        // Export Streaming Logic
        const exportBtn = document.querySelector('button[name="export"]');
        const exportModal = document.getElementById('exportModal');
        const progressBar = document.getElementById('exportProgressBar');
        const exportCount = document.getElementById('exportCount');
        const exportPercent = document.getElementById('exportPercent');
        const closeExportBtn = document.getElementById('closeExportModal');

        if (exportBtn) {
            exportBtn.addEventListener('click', async function (e) {
                e.preventDefault();

                // Show Modal
                exportModal.style.display = 'block';
                progressBar.style.width = '0%';
                exportCount.textContent = 'Starting...';
                exportPercent.textContent = '0%';
                closeExportBtn.style.display = 'none';

                // Build URL
                const form = this.closest('form');
                const formData = new FormData(form);
                formData.append('export', '1'); // Ensure export flag is sent
                const params = new URLSearchParams(formData);
                const url = form.action + '?' + params.toString();

                try {
                    const response = await fetch(url);
                    const reader = response.body.getReader();
                    const totalRecords = parseInt(response.headers.get('X-Total-Count') || 0);

                    let receivedLength = 0; // bytes received
                    let chunks = []; // array of received binary chunks (comprises the body)
                    let itemsProcessed = 0;

                    while (true) {
                        const { done, value } = await reader.read();

                        if (done) {
                            break;
                        }

                        chunks.push(value);
                        receivedLength += value.length;

                        // Count newlines in this chunk to estimate records
                        // Note: This is an approximation if lines are split across chunks, but good enough for progress bar
                        for (let i = 0; i < value.length; i++) {
                            if (value[i] === 10) itemsProcessed++; // 10 is newline char code
                        }

                        // Update UI
                        if (totalRecords > 0) {
                            const percent = Math.min(100, Math.round((itemsProcessed / totalRecords) * 100));
                            progressBar.style.width = percent + '%';
                            exportPercent.textContent = percent + '%';
                            exportCount.textContent = itemsProcessed.toLocaleString() + ' / ' + totalRecords.toLocaleString();
                        } else {
                            // Indeterminate state if header missing
                            exportCount.textContent = itemsProcessed.toLocaleString() + ' exported';
                        }
                    }

                    // Done reading
                    progressBar.style.width = '100%';
                    exportPercent.textContent = '100%';

                    // Create Blob and Download
                    const blob = new Blob(chunks, { type: 'text/plain' });
                    const downloadUrl = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = downloadUrl;
                    a.download = 'tokens_' + new Date().toISOString().slice(0, 19).replace(/T|:/g, '-') + '.txt';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(downloadUrl);

                    // Close modal after short delay
                    setTimeout(() => {
                        exportModal.style.display = 'none';
                    }, 1000);

                } catch (err) {
                    console.error('Export failed:', err);
                    alert('Export failed! See console for details.');
                    exportModal.style.display = 'none';
                }
            });
        }

        // Search Loading Logic
        const filterBtn = document.getElementById('btnFilter');
        const searchModal = document.getElementById('searchModal');
        const filterForm = document.getElementById('tokensFilterForm');

        if (filterForm && filterBtn && searchModal) {
            filterForm.addEventListener('submit', function (e) {
                // Determine if Export was clicked (it has name="export")
                // We only show search modal if it's NOT export

                // Robust check: check if the submitter was the export button
                if (e.submitter && e.submitter.id === 'btnExport') {
                    return;
                }

                searchModal.style.display = 'block';
            });
        }

        // Infinite Scroll Logic
        let nextPageUrl = "{{ $paginatedUsers->nextPageUrl() }}";
        const container = document.getElementById('user-list-container');
        const sentinel = document.getElementById('scroll-sentinel');
        const shimmer = document.getElementById('shimmer-loader');
        let isLoading = false;

        if (sentinel && container && nextPageUrl) {
            const observer = new IntersectionObserver(async (entries) => {
                if (entries[0].isIntersecting && !isLoading && nextPageUrl) {
                    isLoading = true;
                    shimmer.style.display = 'block'; // Show Shimmer

                    try {
                        const response = await fetch(nextPageUrl, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await response.json();

                        // Append new content
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = data.html;
                        while (tempDiv.firstChild) {
                            // Since partial row contains cols, just append them to container row
                             container.appendChild(tempDiv.firstChild);
                        }

                        // Update state
                        if (data.hasMore) {
                            // Extract page number and increment
                             const url = new URL(nextPageUrl, window.location.origin);
                             const currentPage = parseInt(url.searchParams.get('page')) || 1;
                             url.searchParams.set('page', currentPage + 1);
                             nextPageUrl = url.toString();
                        } else {
                            nextPageUrl = null;
                            sentinel.textContent = 'End of results';
                            observer.disconnect();
                        }

                    } catch (error) {
                        console.error('Infinite scroll error:', error);
                    } finally {
                        isLoading = false;
                        shimmer.style.display = 'none'; // Hide Shimmer
                    }
                }
            }, {
                rootMargin: '100px' // Trigger slightly before reaching bottom
            });

            observer.observe(sentinel);
        }
    </script>
@endpush