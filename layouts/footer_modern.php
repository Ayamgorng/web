    </div>

    <!-- Support Chat Button -->
    <button class="support-chat-btn" onclick="openSupportChat()">
        <i class="fas fa-comments"></i>
    </button>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Voucher functionality
        function checkVoucher() {
            const code = document.getElementById('voucher-code').value;
            if (!code) {
                alert('Masukkan kode voucher terlebih dahulu');
                return;
            }
            
            fetch('<?= base_url('/api/check-voucher') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?>'
                },
                body: JSON.stringify({ code: code })
            })
            .then(response => response.json())
            .then(data => {
                if (data.valid) {
                    alert(`Voucher valid! Diskon: ${data.voucher.type === 'percentage' ? data.voucher.value + '%' : 'Rp ' + data.voucher.value}`);
                    // Store voucher in session or local storage for checkout
                    sessionStorage.setItem('selected_voucher', JSON.stringify(data.voucher));
                } else {
                    alert(data.message || 'Voucher tidak valid');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengecek voucher');
            });
        }
        
        // Support chat functionality
        function openSupportChat() {
            window.open('<?= base_url('/support/chat') ?>', 'support_chat', 'width=400,height=600,scrollbars=yes,resizable=yes');
        }
        
        // Flash sale countdown timer
        function updateFlashSaleTimers() {
            const timers = document.querySelectorAll('.flash-sale-timer');
            timers.forEach(timer => {
                const endTime = new Date(timer.dataset.endTime).getTime();
                const now = new Date().getTime();
                const distance = endTime - now;
                
                if (distance > 0) {
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    
                    timer.innerHTML = `${days}d ${hours}h ${minutes}m ${seconds}s`;
                } else {
                    timer.innerHTML = 'BERAKHIR';
                    timer.classList.add('bg-secondary');
                    timer.classList.remove('bg-danger');
                }
            });
        }
        
        // Update timers every second
        setInterval(updateFlashSaleTimers, 1000);
        
        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Mark notifications as read when clicked
        function markNotificationRead(notificationId) {
            fetch('<?= base_url('/api/mark-notification-read') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?>'
                },
                body: JSON.stringify({ id: notificationId })
            });
        }
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Auto-refresh flash sale status every 30 seconds
        setInterval(() => {
            fetch('<?= base_url('/api/flash-sale-status') ?>')
                .then(response => response.json())
                .then(data => {
                    // Update flash sale banner if needed
                    if (data.has_active_sales && !document.querySelector('.flash-sale-banner')) {
                        location.reload(); // Reload to show new flash sales
                    }
                })
                .catch(error => console.error('Error checking flash sale status:', error));
        }, 30000);
        
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Initialize popovers
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        const popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
        
        // Form validation enhancement
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                const forms = document.getElementsByClassName('needs-validation');
                const validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
        
        // Copy to clipboard functionality
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // Show success message
                const toast = document.createElement('div');
                toast.className = 'toast align-items-center text-white bg-success border-0 position-fixed top-0 end-0 m-3';
                toast.style.zIndex = '9999';
                toast.innerHTML = `
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-check me-2"></i>Berhasil disalin ke clipboard!
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                `;
                document.body.appendChild(toast);
                const bsToast = new bootstrap.Toast(toast);
                bsToast.show();
                
                // Remove toast after it's hidden
                toast.addEventListener('hidden.bs.toast', () => {
                    document.body.removeChild(toast);
                });
            });
        }
        
        // Price formatting
        function formatPrice(price) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(price);
        }
        
        // Loading state management
        function showLoading(element) {
            const originalText = element.innerHTML;
            element.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
            element.disabled = true;
            element.dataset.originalText = originalText;
        }
        
        function hideLoading(element) {
            element.innerHTML = element.dataset.originalText;
            element.disabled = false;
        }
        
        // Error handling for AJAX requests
        function handleAjaxError(error) {
            console.error('AJAX Error:', error);
            
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-white bg-danger border-0 position-fixed top-0 end-0 m-3';
            toast.style.zIndex = '9999';
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-exclamation-triangle me-2"></i>Terjadi kesalahan. Silakan coba lagi.
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            document.body.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            toast.addEventListener('hidden.bs.toast', () => {
                document.body.removeChild(toast);
            });
        }
        
        // Page performance monitoring
        window.addEventListener('load', function() {
            const loadTime = performance.timing.domContentLoadedEventEnd - performance.timing.navigationStart;
            console.log('Page load time:', loadTime + 'ms');
            
            // Send performance data to analytics if needed
            if (loadTime > 3000) {
                console.warn('Page load time is slow:', loadTime + 'ms');
            }
        });
    </script>
    
    <?php
    $end_time = microtime(TRUE);
    $execution_time = ($end_time - $start_time);
    if (config('web', 'environment') == 'development') {
        echo "<!-- Page generated in " . number_format($execution_time, 4) . " seconds -->";
    }
    ?>
</body>
</html>
