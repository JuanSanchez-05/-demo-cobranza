/**
 * Sistema de Cobranza - JavaScript Minimalista
 * Funcionalidades elegantes y sutiles
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ============================================
    // Animaciones de entrada sutiles
    // ============================================
    function initAnimations() {
        const animatedElements = document.querySelectorAll('.stat-card, .card, .prestamo-card, .summary-card');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, index * 30);
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -30px 0px'
        });
        
        animatedElements.forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(10px)';
            el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            observer.observe(el);
        });
    }
    
    initAnimations();
    
    // ============================================
    // Animaciones de progreso sutiles
    // ============================================
    function animateProgressBars() {
        const progressBars = document.querySelectorAll('.progress-fill, .cliente-progress-fill, .prestamo-progress-fill');
        
        progressBars.forEach(bar => {
            const width = bar.style.width || bar.getAttribute('data-width') || '0%';
            bar.style.width = '0%';
            bar.setAttribute('data-width', width);
            
            setTimeout(() => {
                bar.style.transition = 'width 0.8s cubic-bezier(0.4, 0, 0.2, 1)';
                bar.style.width = width;
            }, 200);
        });
    }
    
    setTimeout(animateProgressBars, 300);
    
    // ============================================
    // Efectos hover sutiles
    // ============================================
    const cards = document.querySelectorAll('.stat-card, .card, .prestamo-card, .summary-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.2s ease';
        });
    });
    
    // ============================================
    // Auto-hide alerts
    // ============================================
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach((alert, index) => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateX(10px)';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
    
    // ============================================
    // Filtros dinámicos
    // ============================================
    const filterButtons = document.querySelectorAll('.filters .btn');
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            filterButtons.forEach(b => {
                b.classList.remove('btn-primary', 'btn-success', 'btn-warning');
                b.classList.add('btn-secondary');
            });
            
            this.classList.remove('btn-secondary');
            if (this.textContent.includes('Cobradas')) {
                this.classList.add('btn-success');
            } else if (this.textContent.includes('No Cobradas') || this.textContent.includes('Pendientes')) {
                this.classList.add('btn-warning');
            } else {
                this.classList.add('btn-primary');
            }
        });
    });
    
    // ============================================
    // Contador animado sutil
    // ============================================
    function animateValue(element, start, end, duration) {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const current = Math.floor(progress * (end - start) + start);
            
            if (element.textContent.includes('$')) {
                element.textContent = '$' + current.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            } else {
                element.textContent = current.toLocaleString('es-MX');
            }
            
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }
    
    const statValues = document.querySelectorAll('.stat-value, .summary-value');
    statValues.forEach(stat => {
        const text = stat.textContent.replace(/[^0-9.]/g, '');
        const value = parseFloat(text);
        if (!isNaN(value) && value > 0) {
            stat.setAttribute('data-value', value);
            const originalText = stat.textContent;
            stat.textContent = originalText.replace(value.toString(), '0');
            setTimeout(() => {
                animateValue(stat, 0, value, 1200);
            }, 500);
        }
    });
    
    // ============================================
    // Mejoras de UX en formularios
    // ============================================
    const formInputs = document.querySelectorAll('input, select, textarea');
    formInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
            if (this.value) {
                this.parentElement.classList.add('has-value');
            } else {
                this.parentElement.classList.remove('has-value');
            }
        });
        
        input.addEventListener('input', function() {
            if (this.checkValidity()) {
                this.style.borderColor = 'var(--gray-600)';
                setTimeout(() => {
                    this.style.borderColor = '';
                }, 1500);
            }
        });
    });
    
    // ============================================
    // Tooltips minimalistas
    // ============================================
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    tooltipElements.forEach(element => {
        let tooltip = null;
        
        element.addEventListener('mouseenter', function(e) {
            tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.getAttribute('data-tooltip');
            tooltip.style.cssText = `
                position: fixed;
                background: rgba(0, 0, 0, 0.9);
                color: white;
                padding: 8px 12px;
                border-radius: 4px;
                font-size: 12px;
                font-weight: 500;
                z-index: 10000;
                pointer-events: none;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                opacity: 0;
                transform: translateY(5px);
                transition: all 0.2s ease;
                max-width: 250px;
            `;
            
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 8) + 'px';
            tooltip.style.left = (rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)) + 'px';
            
            setTimeout(() => {
                tooltip.style.opacity = '1';
                tooltip.style.transform = 'translateY(0)';
            }, 10);
        });
        
        element.addEventListener('mouseleave', function() {
            if (tooltip) {
                tooltip.style.opacity = '0';
                tooltip.style.transform = 'translateY(5px)';
                setTimeout(() => {
                    tooltip.remove();
                    tooltip = null;
                }, 200);
            }
        });
    });
    
    // ============================================
    // Smooth scroll
    // ============================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const offset = 80;
                const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - offset;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // ============================================
    // Navbar que se oculta al hacer scroll
    // ============================================
    let lastScroll = 0;
    const navbar = document.querySelector('.navbar');
    
    if (navbar) {
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > lastScroll && currentScroll > 100) {
                navbar.style.transform = 'translateY(-100%)';
            } else {
                navbar.style.transform = 'translateY(0)';
            }
            
            lastScroll = currentScroll;
        });
    }

    // ============================================
    // Toggle del menú en móviles (con overlay y animación)
    // ============================================
    const navbarToggle = document.querySelector('.navbar-toggle');
    const navbarMenu = document.querySelector('.navbar-menu');
    const navbarOverlay = document.querySelector('.navbar-overlay');
    if (navbarToggle && navbarMenu) {
        navbarToggle.addEventListener('click', () => {
            navbarMenu.classList.toggle('open');
            if (navbarOverlay) navbarOverlay.classList.toggle('show');
            // cambiar icono hamburguesa/cerrar
            if (navbarToggle.classList.toggle('active')) {
                navbarToggle.innerHTML = '&times;';
            } else {
                navbarToggle.innerHTML = '&#9776;';
            }
        });
    }
    if (navbarOverlay && navbarMenu && navbarToggle) {
        navbarOverlay.addEventListener('click', () => {
            navbarMenu.classList.remove('open');
            navbarOverlay.classList.remove('show');
            navbarToggle.classList.remove('active');
            navbarToggle.innerHTML = '&#9776;';
        });
    }
    
    // ============================================
    // Loading states para botones
    // ============================================
    const submitButtons = document.querySelectorAll('button[type="submit"], .btn[type="submit"]');
    submitButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.closest('form') && this.closest('form').checkValidity()) {
                this.style.opacity = '0.6';
                this.style.pointerEvents = 'none';
                const originalText = this.innerHTML;
                this.innerHTML = 'Procesando...';
                
                setTimeout(() => {
                    this.style.opacity = '';
                    this.style.pointerEvents = '';
                    this.innerHTML = originalText;
                }, 2000);
            }
        });
    });
    
    // ============================================
    // Efecto sutil en botones
    // ============================================
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                border-radius: 50%;
                background: rgba(0, 0, 0, 0.1);
                left: ${x}px;
                top: ${y}px;
                transform: scale(0);
                animation: ripple 0.4s ease-out;
                pointer-events: none;
            `;
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 400);
        });
    });
    
    // Añadir animación ripple
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
    
    console.log('✅ Sistema de Cobranza - Cargado correctamente');
    
});
