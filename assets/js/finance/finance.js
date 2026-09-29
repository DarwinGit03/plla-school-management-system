document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-auto-dismiss').forEach(function (alertElement) {
        window.setTimeout(function () {
            if (window.bootstrap && window.bootstrap.Alert) {
                window.bootstrap.Alert.getOrCreateInstance(alertElement).close();
            } else {
                alertElement.remove();
            }
        }, 2000);
    });

    const voidModal = document.getElementById('voidPaymentModal');
    if (voidModal) {
        voidModal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            voidModal.querySelector('#voidPaymentId').value = trigger.dataset.paymentId;
            voidModal.querySelector('#voidPaymentReceipt').textContent = 'Receipt ' + trigger.dataset.receipt;
            voidModal.querySelector('#voidPaymentReason').value = '';
        });
    }

    const correctionModal = document.getElementById('correctPaymentModal');
    if (correctionModal) {
        correctionModal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            correctionModal.querySelector('#correctPaymentId').value = trigger.dataset.paymentId;
            correctionModal.querySelector('#correctPaymentReceipt').textContent = 'Receipt ' + trigger.dataset.receipt;
            const methodSelect = correctionModal.querySelector('#correctPaymentMethod');
            const storedMethod = (trigger.dataset.method || '').trim().toLowerCase().replace(/[ -]+/g, '_');
            const methodAliases = { banktransfer: 'bank_transfer', g_cash: 'gcash' };
            methodSelect.value = methodAliases[storedMethod] || storedMethod;
            if (methodSelect.selectedIndex < 0) methodSelect.value = '';
            correctionModal.querySelector('#correctPaymentReference').value = trigger.dataset.reference || '';
            correctionModal.querySelector('#correctPaymentReason').value = '';
        });
    }

    const modal = document.getElementById('recordPaymentModal');
    if (!modal) return;

    const feeIdInput = modal.querySelector('#paymentFeeId');
    const description = modal.querySelector('#paymentFeeDescription');
    const balanceLabel = modal.querySelector('#paymentFeeBalance');
    const amountInput = modal.querySelector('#amountPaid');
    const methodSelect = modal.querySelector('#paymentMethod');
    const referenceInput = modal.querySelector('#referenceNumber');
    const currency = new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    });

    modal.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        const balance = Number(trigger.dataset.balance || 0);

        feeIdInput.value = trigger.dataset.feeId;
        description.textContent = [
            trigger.dataset.studentName,
            trigger.dataset.category,
            trigger.dataset.label
        ].filter(Boolean).join(' - ');
        balanceLabel.textContent = currency.format(balance);
        amountInput.max = balance.toFixed(2);
        amountInput.value = '';
        methodSelect.value = '';
        referenceInput.value = '';
    });
});
