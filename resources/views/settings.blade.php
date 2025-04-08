<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thiết lập Vòng Quay</title>
    <script src="https://cdn.tailwindcss.com/3.4.1"></script>
</head>
<body class="font-sans flex flex-col items-center w-full min-h-screen bg-gray-100 gap-5 p-4" style="background-size: cover;">
    <div class="w-full max-w-md">
        <a href="/" class="inline-block mb-4 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded">Quay lại trang chính</a>
        <div class="bg-white p-4 rounded-lg shadow-xl w-full">
            <h3 class="text-xl font-semibold mb-3">Quản lý vòng quay</h3>
            <div class="flex flex-wrap gap-2 mb-3">
                <button onclick="addNewWheel()" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded text-sm">Thêm vòng quay mới</button>
                <select id="wheelSelector" onchange="switchWheel(this.value)" class="p-2 border rounded text-sm"></select>
                <button id="deleteWheelBtn" onclick="deleteWheel()" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded text-sm hidden">Xóa vòng quay</button>
            </div>
            <h3 class="text-xl font-semibold mb-3">Tùy chỉnh vòng quay hiện tại</h3>
            <div id="items" class="space-y-3"></div>
            <div class="mt-4 flex flex-wrap gap-2">
                <button onclick="addItem()" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded text-sm">Thêm mục</button>
                <button onclick="updateWheel()" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded text-sm">Cập nhật vòng quay</button>
                <button onclick="restoreDefaultItems()" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded text-sm">Khôi phục các mục mặc định</button>
            </div>
            <h3 class="text-xl font-semibold mt-4 mb-3">Đổi giao diện</h3>
            <div class="flex flex-wrap gap-2">
                <button onclick="changeBackground('./img/background.jpg')" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded text-sm">Mặc định</button>
                <button onclick="changeBackground('./img/background1.jpg')" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded text-sm">Giao diện 1</button>
                <button onclick="changeBackground('./img/background2.jpg')" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded text-sm">Giao diện 2</button>
                <button onclick="changeBackground('./img/background3.jpg')" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded text-sm">Giao diện 3</button>
                <button onclick="changeBackground('./img/background4.jpg')" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded text-sm">Giao diện 4</button>
            </div>
        </div>
    </div>

    <script>
        const defaultWheel = {
            id: 'wheel-0',
            name: 'Vòng quay mặc định',
            items: [
                { name: "Trúng lớn", weight: 5 },
                { name: "Trúng vừa", weight: 10 },
                { name: "Trúng nhỏ", weight: 20 },
                { name: "Chúc may mắn lần sau", weight: 65 }
            ]
        };
        let wheels = [];
        let currentWheelId = 'wheel-0';
        let items = [];
        const API_BASE_URL = '/api';

        async function fetchWheels() {
            try {
                const response = await fetch(`${API_BASE_URL}/wheels`, { cache: 'no-store' });
                if (!response.ok) throw new Error('Không thể tải danh sách vòng quay');
                const data = await response.json();
                wheels = data.wheels;
                if (wheels.length === 0) {
                    wheels = [defaultWheel];
                    await fetch(`${API_BASE_URL}/wheels`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(defaultWheel)
                    });
                    await fetchWheels(); // Gọi lại để lấy dữ liệu mới
                    return;
                }
                currentWheelId = wheels.some(w => w.id === data.current_wheel_id) ? data.current_wheel_id : 'wheel-0';
                items = wheels.find(w => w.id === currentWheelId)?.items || defaultWheel.items;
                updateWheelSelector();
                renderItems();
                toggleDeleteButton();
            } catch (error) {
                console.error('Error fetching wheels:', error);
            }
        }

        async function updateWheel() {
            try {
                const inputs = document.querySelectorAll('#items > div');
                items = Array.from(inputs).map(input => ({
                    name: input.querySelector('input[type="text"]').value,
                    weight: parseInt(input.querySelector('input[type="number"]').value) || 0
                })).filter(item => item.name);

                const currentWheel = wheels.find(w => w.id === currentWheelId);
                currentWheel.items = items;
                const response = await fetch(`${API_BASE_URL}/wheels/${currentWheelId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(currentWheel)
                });
                if (!response.ok) throw new Error('Không thể cập nhật vòng quay');
                await fetchWheels(); // Đồng bộ lại dữ liệu
            } catch (error) {
                console.error('Error updating wheel:', error);
            }
        }

        function addItem() {
            const itemsDiv = document.getElementById('items');
            const newItem = document.createElement('div');
            newItem.className = 'flex gap-2 items-center';
            newItem.innerHTML = `
                <input type="text" value="" placeholder="Tên mục" class="p-1 border rounded w-32 text-sm">
                <input type="number" value="10" min="0" max="100" placeholder="Tỷ lệ (%)" class="p-1 border rounded w-20 text-sm">
                <button class="px-2 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-sm" onclick="removeItem(this)">Xóa</button>
            `;
            itemsDiv.appendChild(newItem);
        }

        function removeItem(button) {
            if (document.querySelectorAll('#items > div').length > 1) {
                button.parentElement.remove();
                updateWheel();
            }
        }

        async function restoreDefaultItems() {
            try {
                const currentWheel = wheels.find(w => w.id === currentWheelId);
                if (!currentWheel) throw new Error('Không tìm thấy vòng quay hiện tại');
                currentWheel.items = [...defaultWheel.items];
                const response = await fetch(`${API_BASE_URL}/wheels/${currentWheelId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(currentWheel)
                });
                if (!response.ok) throw new Error('Không thể khôi phục mục mặc định');
                const updatedWheel = await response.json();
                items = updatedWheel.items;
                console.log('Restored items:', items);
                renderItems();
                await fetchWheels();
            } catch (error) {
                console.error('Error restoring default items:', error);
                items = [...defaultWheel.items];
                renderItems();
            }
        }

        function renderItems() {
            const itemsDiv = document.getElementById('items');
            itemsDiv.innerHTML = items.map(item => `
                <div class="flex gap-2 items-center">
                    <input type="text" value="${item.name}" placeholder="Tên mục" class="p-1 border rounded w-32 text-sm">
                    <input type="number" value="${item.weight}" min="0" max="100" placeholder="Tỷ lệ (%)" class="p-1 border rounded w-20 text-sm">
                    <button class="px-2 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-sm" onclick="removeItem(this)">Xóa</button>
                </div>
            `).join('');
        }

        async function addNewWheel() {
            try {
                // Tìm số thứ tự lớn nhất từ các ID hiện có
                const maxIndex = wheels.reduce((max, wheel) => {
                    const index = parseInt(wheel.id.split('-')[1] || 0);
                    return Math.max(max, index);
                }, -1);
                const newWheelId = `wheel-${maxIndex + 1}`;
                const newWheel = {
                    id: newWheelId,
                    name: `Vòng quay ${maxIndex + 2}`, // +2 vì maxIndex bắt đầu từ 0
                    items: [...defaultWheel.items]
                };
                const response = await fetch(`${API_BASE_URL}/wheels`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(newWheel)
                });
                if (!response.ok) throw new Error('Không thể tạo vòng quay mới');
                await fetchWheels();
                switchWheel(newWheelId);
            } catch (error) {
                console.error('Error adding new wheel:', error);
            }
        }

        async function switchWheel(wheelId) {
            try {
                currentWheelId = wheelId;
                const response = await fetch(`${API_BASE_URL}/current-wheel`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ current_wheel_id: wheelId })
                });
                if (!response.ok) throw new Error('Không thể cập nhật vòng quay hiện tại');
                items = wheels.find(w => w.id === currentWheelId)?.items || defaultWheel.items;
                renderItems();
                toggleDeleteButton();
            } catch (error) {
                console.error('Error switching wheel:', error);
            }
        }

        async function deleteWheel() {
            try {
                if (currentWheelId === 'wheel-0') {
                    alert('Không thể xóa vòng quay mặc định!');
                    return;
                }
                const response = await fetch(`${API_BASE_URL}/wheels/${currentWheelId}`, {
                    method: 'DELETE'
                });
                if (!response.ok) throw new Error('Không thể xóa vòng quay');
                await fetchWheels();
                switchWheel('wheel-0');
            } catch (error) {
                console.error('Error deleting wheel:', error);
            }
        }

        function toggleDeleteButton() {
            const deleteBtn = document.getElementById('deleteWheelBtn');
            deleteBtn.classList.toggle('hidden', currentWheelId === 'wheel-0');
        }

        function updateWheelSelector() {
            const selector = document.getElementById('wheelSelector');
            selector.innerHTML = wheels.map(wheel => `
                <option value="${wheel.id}" ${wheel.id === currentWheelId ? 'selected' : ''}>${wheel.name}</option>
            `).join('');
            toggleDeleteButton();
        }

        async function changeBackground(bgUrl) {
            try {
                document.body.style.backgroundImage = `url('${bgUrl}')`;
                const response = await fetch(`${API_BASE_URL}/background`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ background: bgUrl })
                });
                if (!response.ok) throw new Error('Không thể lưu background');
            } catch (error) {
                console.error('Error saving background:', error);
                alert('Có lỗi khi lưu background. Vui lòng thử lại!');
            }
        }

        async function fetchBackground() {
            try {
                const response = await fetch(`${API_BASE_URL}/background`);
                if (!response.ok) throw new Error('Không thể tải background');
                const data = await response.json();
                const bgUrl = data.background || './img/background.jpg';
                document.body.style.backgroundImage = `url('${bgUrl}')`;
            } catch (error) {
                console.error('Error fetching background:', error);
            }
        }

        window.onload = async () => {
            await fetchBackground();
            await fetchWheels();
        };
    </script>
</body>
</html>