// ============================================
// COMPARE MANAGER
// Manages product comparison
// ============================================

class CompareManager {
    constructor() {
        // вземаме текущия потребител (ако има такъв) и използваме неговото ID за ключа в localStorage
        const user = this.getCurrentUser();
        const userId = user ? user.user_id : 'guest';
        
        // User-specific storage key
        this.storageKey = `klimatici_compare_${userId}`;
        this.maxProducts = 4; // Maximum products to compare
        this.compareList = this.loadCompare();
        
        // Migrate old global compare to user-specific (one-time)
        this.migrateOldCompare();
    }
    
    //вземане на текущия потребител от localStorage (ако е логнат)
    getCurrentUser() {
        try {
            const userData = localStorage.getItem('user');
            return userData ? JSON.parse(userData) : null;
        } catch (error) {
            return null;
        }
    }
    
    // Migrate old global compare to user-specific storage (one-time migration)
    migrateOldCompare() {
        const oldKey = 'klimatici_compare';
        const oldData = localStorage.getItem(oldKey);
        
        // Only migrate if old key exists and current key is empty
        if (oldData && !localStorage.getItem(this.storageKey)) {
            try {
                const user = this.getCurrentUser();
                // Only migrate if logged in (don't migrate to guest)
                if (user && user.user_id) {
                    localStorage.setItem(this.storageKey, oldData);
                    console.log('Migrated compare to user-specific storage');
                }
                // Remove old global key
                localStorage.removeItem(oldKey);
            } catch (error) {
                console.error('Error migrating compare:', error);
            }
        }
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

    // добавяне на продукт за сравнение
    addToCompare(product) {
        // Check if already in compare
        if (this.isInCompare(product.product_id)) {
            return { success: false, message: 'Продуктът вече е добавен за сравнение' };
        }

        // проверка за максимален брой продукти
        if (this.compareList.length >= this.maxProducts) {
            return { 
                success: false, 
                message: `Можете да сравнявате максимум ${this.maxProducts} продукта` 
            };
        }

        // добавяне на продукта в списъка за сравнение (с всички необходими данни)
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

    // махаме продукт от сравнение
    removeFromCompare(productId) {
        const initialLength = this.compareList.length;
        this.compareList = this.compareList.filter(item => item.product_id !== productId);
        
        if (this.compareList.length < initialLength) {
            this.saveCompare();
            return true;
        }
        return false;
    }

    //превключваме състоянието на продукта в сравнение (добавяне/премахване)
    toggleCompare(product) {
        if (this.isInCompare(product.product_id)) {
            this.removeFromCompare(product.product_id);
            return { success: true, added: false, message: 'Премахнато от сравнение' };
        } else {
            const result = this.addToCompare(product);
            return { ...result, added: result.success };
        }
    }

    // проверка дали продуктът е в списъка за сравнение
    isInCompare(productId) {
        return this.compareList.some(item => item.product_id === productId);
    }

    // вземане на текущия списък за сравнение
    getCompareList() {
        return this.compareList;
    }

    //вземане на броя на продуктите в сравнение
    getCount() {
        return this.compareList.length;
    }

    // проверка дали може да се добави още продукт за сравнение (до максимум 4)
    canAddMore() {
        return this.compareList.length < this.maxProducts;
    }

    // изчистване на списъка за сравнение
    clearCompare() {
        this.compareList = [];
        this.saveCompare();
    }

    // обновяване на броячите на значки в навигацията
    updateBadges() {
        const badges = document.querySelectorAll('.compare-badge');
        const count = this.getCount();
        
        badges.forEach(badge => {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'block' : 'none';
        });
    }

    // превключване на състоянието на бутоните за сравнение на страницата
    triggerUpdate() {
        window.dispatchEvent(new CustomEvent('compareUpdated', {
            detail: {
                compareList: this.compareList,
                count: this.getCount()
            }
        }));
    }

    // актуализиране на иконите на бутоните за сравнение (оцветяване при добавяне)
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

// създаване на глобален екземпляр на CompareManager
const Compare = new CompareManager();

// експортиране на CompareManager за използване в други модули (ако е необходимо)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CompareManager;
}