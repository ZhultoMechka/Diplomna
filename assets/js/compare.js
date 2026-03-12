// ============================================
// COMPARE MANAGER
// Manages product comparison
// ============================================

class CompareManager {
    constructor() {
        this.storageKey = 'klimatici_compare';
        this.maxProducts = 4; // Maximum products to compare
        this.compareList = this.loadCompare();
    }

    // Load compare list from localStorage
    loadCompare() {
        try {
            const data = localStorage.getItem(this.storageKey);
            return data ? JSON.parse(data) : [];
        } catch (error) {
            console.error('Error loading compare list:', error);
            return [];
        }
    }

    // Save compare list to localStorage
    saveCompare() {
        try {
            localStorage.setItem(this.storageKey, JSON.stringify(this.compareList));
            this.updateBadges();
            this.triggerUpdate();
        } catch (error) {
            console.error('Error saving compare list:', error);
        }
    }

    // Add product to compare
    addToCompare(product) {
        // Check if already in compare
        if (this.isInCompare(product.product_id)) {
            return { success: false, message: 'Продуктът вече е добавен за сравнение' };
        }

        // Check max limit
        if (this.compareList.length >= this.maxProducts) {
            return { 
                success: false, 
                message: `Можете да сравнявате максимум ${this.maxProducts} продукта` 
            };
        }

        // Add to compare
        this.compareList.push({
            product_id: product.product_id,
            product_name: product.product_name,
            brand: product.brand,
            price: product.price,
            main_image_url: product.main_image_url || 'images/placeholder.jpg',
            btu_capacity: product.btu_capacity,
            energy_class: product.energy_class,
            cooling_capacity: product.cooling_capacity,
            heating_capacity: product.heating_capacity,
            noise_level_indoor: product.noise_level_indoor,
            noise_level_outdoor: product.noise_level_outdoor,
            weight: product.weight,
            dimensions: product.dimensions,
            refrigerant: product.refrigerant,
            wifi_enabled: product.wifi_enabled,
            added_at: new Date().toISOString()
        });

        this.saveCompare();
        return { success: true, message: 'Продуктът е добавен за сравнение' };
    }

    // Remove product from compare
    removeFromCompare(productId) {
        const initialLength = this.compareList.length;
        this.compareList = this.compareList.filter(item => item.product_id !== productId);
        
        if (this.compareList.length < initialLength) {
            this.saveCompare();
            return true;
        }
        return false;
    }

    // Toggle compare
    toggleCompare(product) {
        if (this.isInCompare(product.product_id)) {
            this.removeFromCompare(product.product_id);
            return { success: true, added: false, message: 'Премахнато от сравнение' };
        } else {
            const result = this.addToCompare(product);
            return { ...result, added: result.success };
        }
    }

    // Check if product is in compare
    isInCompare(productId) {
        return this.compareList.some(item => item.product_id === productId);
    }

    // Get compare list
    getCompareList() {
        return this.compareList;
    }

    // Get compare count
    getCount() {
        return this.compareList.length;
    }

    // Check if can add more products
    canAddMore() {
        return this.compareList.length < this.maxProducts;
    }

    // Clear compare list
    clearCompare() {
        this.compareList = [];
        this.saveCompare();
    }

    // Update badge counters in navigation
    updateBadges() {
        const badges = document.querySelectorAll('.compare-badge');
        const count = this.getCount();
        
        badges.forEach(badge => {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'block' : 'none';
        });
    }

    // Trigger update event for other components
    triggerUpdate() {
        window.dispatchEvent(new CustomEvent('compareUpdated', {
            detail: {
                compareList: this.compareList,
                count: this.getCount()
            }
        }));
    }

    // Update compare icons on page
    updateCompareIcons() {
        const compareBtns = document.querySelectorAll('.compare-btn');
        compareBtns.forEach(btn => {
            const productId = parseInt(btn.dataset.productId);
            const isComparing = this.isInCompare(productId);
            
            const icon = btn.querySelector('i');
            if (icon) {
                icon.style.color = isComparing ? '#FF8800' : '';
            }
            
            btn.classList.toggle('active', isComparing);
        });
    }
}

// Create global instance
const Compare = new CompareManager();

// Export for modules (if needed)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CompareManager;
}