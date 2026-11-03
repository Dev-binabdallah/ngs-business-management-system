document.addEventListener("DOMContentLoaded", function () {
    const productSelect = document.getElementById("product_select");
    const qtyInput = document.getElementById("sale_qty");
    const discountInput = document.getElementById("sale_discount");
    const totalInput = document.getElementById("calculated_total");
    const previewCost = document.getElementById("preview_cost");
    const previewProfit = document.getElementById("preview_profit");

    function calculateRealtimeTotals() {
        if (!productSelect || !qtyInput || !discountInput) return;

        const selected = productSelect.options[productSelect.selectedIndex];
        const basePrice = parseFloat(selected.getAttribute("data-price")) || 0;
        const unitCost = parseFloat(selected.getAttribute("data-cost")) || 0;
        const stockAvailable = parseInt(selected.getAttribute("data-stock")) || 0;

        let qty = parseInt(qtyInput.value) || 0;
        let discount = parseFloat(discountInput.value) || 0;

        if (stockAvailable > 0 && qty > stockAvailable) {
            alert("Quantity cannot exceed existing stock balance (" + stockAvailable + ")");
            qty = stockAvailable;
            qtyInput.value = qty;
        }

        const netUnitPrice = Math.max(0, basePrice - discount);
        const totalRevenue = qty * netUnitPrice;
        const totalCost = qty * unitCost;
        const grossMargin = totalRevenue - totalCost;

        if (totalInput) totalInput.value = totalRevenue.toFixed(2);
        if (previewCost) previewCost.textContent = totalCost.toFixed(2);
        if (previewProfit) {
            previewProfit.textContent = grossMargin.toFixed(2);
            if (grossMargin < 0) {
                previewProfit.style.color = "#dc2626";
            } else {
                previewProfit.style.color = "#059669";
            }
        }
    }

    if (productSelect) productSelect.addEventListener("change", calculateRealtimeTotals);
    if (qtyInput) qtyInput.addEventListener("input", calculateRealtimeTotals);
    if (discountInput) discountInput.addEventListener("input", calculateRealtimeTotals);
});