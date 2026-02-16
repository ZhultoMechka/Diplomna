// ============================================
// Cart Manager - Управление на количката
// Използва localStorage за съхранение
// ============================================

const CartManager = {
    // Име на localStorage ключа
    STORAGE_KEY: 'klimatici_cart',

    // Вземане на количката
    getCart() {
        const cart = localStorage.getItem(this.STORAGE_KEY);
        return cart ? JSON.parse(cart) : [];
    },

    // Запазване на количката
    saveCart(cart) {
        localStorage.setItem(this.STORAGE_KEY, JSON.stringify(cart));
        this.updateCartBadge();
    },

    // Добавяне на продукт в количката
    addProduct(product, quantity = 1, services = []) {
        const cart = this.getCart();
        
        // Изчисляване на цена на услугите
        const servicesPrice = services.reduce((sum, service) => {
            return sum + parseFloat(service.price);
        }, 0);

        // Проверка дали продуктът вече е в количката
        const existingIndex = cart.findIndex(item => 
            item.product.product_id === product.product_id &&
            JSON.stringify(item.services) === JSON.stringify(services)
        );

        if (existingIndex !== -1) {
            // Ако продуктът съществува, увеличаваме количеството
            cart[existingIndex].quantity += quantity;
        } else {
            // Добавяме нов продукт
            cart.push({
                product: product,
                quantity: quantity,
                services: services,
                servicesPrice: servicesPrice,
                unitPrice: parseFloat(product.price),
                totalPrice: (parseFloat(product.price) + servicesPrice) * quantity
            });
        }

        this.saveCart(cart);
        return true;
    },

    // Обновяване на количество
    updateQuantity(index, newQuantity) {
        if (newQuantity < 1) return false;

        const cart = this.getCart();
        
        if (index >= 0 && index < cart.length) {
            cart[index].quantity = newQuantity;
            
            // Преизчисляване на общата цена
            const itemPrice = cart[index].unitPrice + cart[index].servicesPrice;
            cart[index].totalPrice = itemPrice * newQuantity;
            
            this.saveCart(cart);
            return true;
        }
        
        return false;
    },

    // Премахване на продукт
    removeProduct(index) {
        const cart = this.getCart();
        
        if (index >= 0 && index < cart.length) {
            cart.splice(index, 1);
            this.saveCart(cart);
            return true;
        }
        
        return false;
    },

    // Изчистване на цялата количка
    clearCart() {
        localStorage.removeItem(this.STORAGE_KEY);
        this.updateCartBadge();
    },

    // Общо количество продукти
    getTotalItems() {
        const cart = this.getCart();
        return cart.reduce((total, item) => total + item.quantity, 0);
    },

    // Обща цена
    getTotalPrice() {
        const cart = this.getCart();
        return cart.reduce((total, item) => total + item.totalPrice, 0);
    },

    // Обновяване на cart badge във всички страници
    updateCartBadge() {
        const badge = document.getElementById('cartCount');
        if (badge) {
            badge.textContent = this.getTotalItems();
        }
    },

    // Брой различни продукти (не количества)
    getProductCount() {
        return this.getCart().length;
    }
};

// Инициализиране при зареждане на страницата
if (typeof window !== 'undefined') {
    window.addEventListener('DOMContentLoaded', () => {
        CartManager.updateCartBadge();
    });
}