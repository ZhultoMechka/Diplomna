// ============================================
// FAVORITES MANAGER

// ============================================

class FavoritesManager {
    constructor() {
        const user = this.getCurrentUser();
        const userId = user ? user.user_id : 'guest';
        
        this.storageKey = `klimatici_favorites_${userId}`;
        this.favorites = this.loadFavorites();
        
        this.migrateOldFavorites();
    }
    
    getCurrentUser() {
        try {
            const userData = localStorage.getItem('user');
            return userData ? JSON.parse(userData) : null;
        } catch (error) {
            return null;
        }
    }
    
    migrateOldFavorites() {
        const oldKey = 'klimatici_favorites';
        const oldData = localStorage.getItem(oldKey);
        
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

    loadFavorites() {
        try {
            const data = localStorage.getItem(this.storageKey);
            return data ? JSON.parse(data) : [];
        } catch (error) {
            console.error('Error loading favorites:', error);
            return [];
        }
    }

    saveFavorites() {
        try {
            localStorage.setItem(this.storageKey, JSON.stringify(this.favorites));
            this.updateBadges();
            this.triggerUpdate();
        } catch (error) {
            console.error('Error saving favorites:', error);
        }
    }

    addFavorite(product) {
        if (this.isFavorite(product.product_id)) {
            return false;
        }

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

    removeFavorite(productId) {
        const initialLength = this.favorites.length;
        this.favorites = this.favorites.filter(item => item.product_id !== productId);
        
        if (this.favorites.length < initialLength) {
            this.saveFavorites();
            return true;
        }
        return false;
    }

    toggleFavorite(product) {
        if (this.isFavorite(product.product_id)) {
            this.removeFavorite(product.product_id);
            return false; // Removed
        } else {
            this.addFavorite(product);
            return true; // Added
        }
    }

    isFavorite(productId) {
        return this.favorites.some(item => item.product_id === productId);
    }

    getFavorites() {
        return this.favorites;
    }

    getCount() {
        return this.favorites.length;
    }

    clearFavorites() {
        this.favorites = [];
        this.saveFavorites();
    }

    updateBadges() {
        const badges = document.querySelectorAll('.favorites-badge');
        const count = this.getCount();
        
        badges.forEach(badge => {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'block' : 'none';
        });
    }

    triggerUpdate() {
        window.dispatchEvent(new CustomEvent('favoritesUpdated', {
            detail: {
                favorites: this.favorites,
                count: this.getCount()
            }
        }));
    }

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

const Favorites = new FavoritesManager();

if (typeof module !== 'undefined' && module.exports) {
    module.exports = FavoritesManager;
}