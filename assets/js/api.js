// File: js/api.js
// Javascript helper object cho việc gọi API

const API = {
    // Gọi GET request
    async get(endpoint) {
        try {
            const response = await fetch(endpoint);
            const text = await response.text();
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Lỗi parse JSON từ:', endpoint, text);
                return null;
            }
        } catch (error) {
            console.error('API GET Error:', error);
            return null;
        }
    },

    // Gọi POST request (Gửi FormData)
    async post(endpoint, data = {}) {
        const formData = new FormData();
        for (const key in data) {
            formData.append(key, data[key]);
        }
        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                body: formData
            });
            const text = await response.text();
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Lỗi parse JSON từ:', endpoint, text);
                return null;
            }
        } catch (error) {
            console.error('API POST Error:', error);
            return null;
        }
    }
};

// Hàm hiển thị thời gian tương đối giống PHP time_elapsed_string
function timeAgo(dateString) {
    if (!dateString) return '';
    // Format mm/dd
    const date = new Date(dateString);
    if(isNaN(date)) return dateString; // Fallback
    const d = date.getDate().toString().padStart(2, '0');
    const m = (date.getMonth() + 1).toString().padStart(2, '0');
    return `${d}/${m}`;
}
