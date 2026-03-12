// ============================================
// CURRENCY CONVERTER
// Converts all prices from BGN to EUR
// ============================================

const CURRENCY_CONFIG = {
    exchangeRate: 1.95583, // 1 EUR = 1.95583 BGN (fixed rate)
    symbol: '€',
    locale: 'bg-BG'
};

// Convert BGN to EUR
function convertToEUR(bgnAmount) {
    return bgnAmount / CURRENCY_CONFIG.exchangeRate;
}

// Format EUR price
function formatEUR(amount) {
    const eurAmount = convertToEUR(amount);
    return eurAmount.toFixed(2) + ' ' + CURRENCY_CONFIG.symbol;
}

// Convert all prices on page
function convertAllPrices() {
    // Find all elements with "лв" or price classes
    const priceElements = document.querySelectorAll(
        '.product-price, .price, [id*="price"], [id*="Price"], ' +
        '.total-price, .service-price, .add-to-cart-btn, ' +
        '.related-price, .cart-item-price'
    );
    
    priceElements.forEach(element => {
        const text = element.textContent || element.innerText;
        
        // Match prices like "1299.00 лв" or "1,299 лв"
        const priceMatch = text.match(/([\d,\.]+)\s*лв/);
        
        if (priceMatch) {
            const bgnPrice = parseFloat(priceMatch[1].replace(',', ''));
            const eurPrice = formatEUR(bgnPrice);
            
            // Replace the price
            element.textContent = text.replace(/([\d,\.]+)\s*лв/, eurPrice);
        }
    });
    
    // Also convert prices in data attributes
    const elementsWithDataPrice = document.querySelectorAll('[data-price]');
    elementsWithDataPrice.forEach(element => {
        const bgnPrice = parseFloat(element.dataset.price);
        if (!isNaN(bgnPrice)) {
            const eurPrice = convertToEUR(bgnPrice);
            element.dataset.priceEur = eurPrice.toFixed(2);
        }
    });
}

// Override price display functions
const originalToFixed = Number.prototype.toFixed;

// Helper to convert price when displaying
function displayPrice(bgnPrice) {
    const eurPrice = convertToEUR(parseFloat(bgnPrice));
    return eurPrice.toFixed(2) + ' €';
}

// Auto-convert on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(convertAllPrices, 100); // Small delay to ensure content is loaded
        
        // Re-convert after 500ms (for dynamically loaded content)
        setTimeout(convertAllPrices, 500);
    });
} else {
    setTimeout(convertAllPrices, 100);
    setTimeout(convertAllPrices, 500);
}

// Watch for changes and re-convert
const observer = new MutationObserver((mutations) => {
    let shouldConvert = false;
    
    mutations.forEach(mutation => {
        if (mutation.addedNodes.length > 0) {
            shouldConvert = true;
        }
        if (mutation.type === 'characterData') {
            const text = mutation.target.textContent;
            if (text && text.includes('лв')) {
                shouldConvert = true;
            }
        }
    });
    
    if (shouldConvert) {
        setTimeout(convertAllPrices, 50);
    }
});

// Start observing
observer.observe(document.body, {
    childList: true,
    subtree: true,
    characterData: true,
    characterDataOldValue: true
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { convertToEUR, formatEUR, displayPrice };
}

// Global helpers
window.convertToEUR = convertToEUR;
window.formatEUR = formatEUR;
window.displayPrice = displayPrice;