// Utilitaires globaux
window.PremiumUX = {
    // Notification toast premium
    toast: (message, type = 'info', duration = 3000) => {
        const toast = document.createElement('div');
        const colors = {
            success: '#10b981',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#0752dd'
        };

        const row = document.createElement('div');
        row.style.display = 'flex';
        row.style.alignItems = 'center';
        row.style.gap = '12px';

        const icon = document.createElement('i');
        const iconName = type === 'success'
            ? 'check-circle'
            : type === 'error'
                ? 'times-circle'
                : type === 'warning'
                    ? 'exclamation-triangle'
                    : 'info-circle';
        icon.className = `fas fa-${iconName}`;

        const label = document.createElement('span');
        label.textContent = String(message);

        row.appendChild(icon);
        row.appendChild(label);
        toast.appendChild(row);

        toast.style.cssText = `
            position: fixed;
            bottom: 24px;
            right: 24px;
            padding: 16px 24px;
            background: ${colors[type] || colors.info};
            color: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            z-index: 99999;
            font-weight: 500;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        `;

        document.body.appendChild(toast);

        // Animation d'entrée
        requestAnimationFrame(() => {
            toast.style.transform = 'translateY(0)';
            toast.style.opacity = '1';
        });

        // Animation de sortie
        setTimeout(() => {
            toast.style.transform = 'translateY(100px)';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 400);
        }, duration);
    },

    // Confirmation premium
    confirm: (message, onConfirm, onCancel) => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Confirmation',
                text: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0752dd',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Confirmer',
                cancelButtonText: 'Annuler',
                customClass: {
                    popup: 'premium-swal-popup'
                }
            }).then((result) => {
                if (result.isConfirmed && onConfirm) {
                    onConfirm();
                } else if (onCancel) {
                    onCancel();
                }
            });
        } else if (confirm(message)) {
            onConfirm && onConfirm();
        } else {
            onCancel && onCancel();
        }
    }
};
