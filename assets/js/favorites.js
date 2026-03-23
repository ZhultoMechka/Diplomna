// ============================================
// FAVORITES MANAGER
// Manages favorite/wishlist products
// ============================================

class FavoritesManager {
    constructor() {
        // Get current user for user-specific storage
        const user = this.getCurrentUser();
        const userId = user ? user.user_id : 'guest';
        
        // User-specific storage key
        this.storageKey = `klimatici_favorites_${userId}`;
        this.favorites = this.loadFavorites();
        
        // Migrate old global favorites to user-specific (one-time)
        this.migrateOldFavorites();
    }
    
    // Get current logged in user
    getCurrentUser() {
        try {
            const userData = localStorage.getItem('user');
            return userData ? JSON.parse(userData) : null;
        } catch (error) {
            return null;
        }
    }
    
    // Migrate old global favorites to user-specific storage (one-time migration)
    migrateOldFavorites() {
        const oldKey = 'klimatici_favorites';
        const oldData = localStorage.getItem(oldKey);
        
        // Only migrate if old key exists and current key is empty
        if (oldData && !localStorage.getItem(this.storageKey)) {
            try {
                const user = this.getCurrentUser();
                // Only migrate if logged in (don't migrate to guest)
                if (user && user.user_id) {
                    localStorage.setItem(this.storageKey, oldData);
                    console.log('Migrated favorites to user-specific storage');
                }
                // Remove old global key
                localStorage.removeItem(oldKey);
            } catch (error) {
                console.error('Error migrating favorites:', error);
            }
        }
    }

    // Load favorites from localStorage
    loadFavorites() {
        try {
            const data = localStorage.getItem(this.storageKey);
            return data ? JSON.parse(data) : [];
        } catch (error) {
            console.error('Error loading favorites:', error);
            return [];
        }
    }

    // Save favorites to localStorage
    saveFavorites() {
        try {
            localStorage.setItem(this.storageKey, JSON.stringify(this.favorites));
            this.updateBadges();
            this.triggerUpdate();
        } catch (error) {
            console.error('Error saving favorites:', error);
        }
    }

    // Add product to favorites
    addFavorite(product) {
        // Check if already exists
        if (this.isFavorite(product.product_id)) {
            return false;
        }

        // Add to favorites
        this.favorites.push({
            product_id: product.product_id,
            product_name: product.product_name,
            brand: product.brand,
            price: product.price,
            main_image_url: product.main_image_url || 'images/placeholder.jpg',
            btu_capacity: product.btu_capacity,
            energy_class: product.energy_class,
            added_at: new Date().toISOString()
        });

        this.saveFavorites();
        return true;
    }

    // Remove product from favorites
    removeFavorite(productId) {
        const initialLength = this.favorites.length;
        this.favorites = this.favorites.filter(item => item.product_id !== productId);
        
        if (this.favorites.length < initialLength) {
            this.saveFavorites();
            return true;
        }
        return false;
    }

    // Toggle favorite
    toggleFavorite(product) {
        if (this.isFavorite(product.product_id)) {
            this.removeFavorite(product.product_id);
            return false; // Removed
        } else {
            this.addFavorite(product);
            return true; // Added
        }
    }

    // Check if product is favorite
    isFavorite(productId) {
        return this.favorites.some(item => item.product_id === productId);
    }

    // Get all favorites
    getFavorites() {
        return this.favorites;
    }

    // Get favorites count
    getCount() {
        return this.favorites.length;
    }

    // Clear all favorites
    clearFavorites() {
        this.favorites = [];
        this.saveFavorites();
    }

    // Update badge counters in navigation
    updateBadges() {
        const badges = document.querySelectorAll('.favorites-badge');
        const count = this.getCount();
        
        badges.forEach(badge => {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'block' : 'none';
        });
    }

    // Trigger update event for other components
    triggerUpdate() {
        window.dispatchEvent(new CustomEvent('favoritesUpdated', {
            detail: {
                favorites: this.favorites,
                count: this.getCount()
            }
        }));
    }

    // Update heart icons on page
    updateHeartIcons() {
        const heartBtns = document.querySelectorAll('.favorite-btn');
        heartBtns.forEach(btn => {
            const productId = parseInt(btn.dataset.productId);
            const isFav = this.isFavorite(productId);
            
            const icon = btn.querySelector('i');
            if (icon) {
                icon.className = isFav ? 'fas fa-heart' : 'far fa-heart';
                icon.style.color = isFav ? '#E74C3C' : '';
            }
            
            btn.classList.toggle('active', isFav);
        });
    }
}

// Create global instance
const Favorites = new FavoritesManager();

// Export for modules (if needed)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FavoritesManager;
}