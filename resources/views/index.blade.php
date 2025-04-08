<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vòng Quay May Mắn</title>
    <script src="https://cdn.tailwindcss.com/3.4.1"></script>
</head>
<body class="font-sans flex flex-col items-center w-full min-h-screen bg-gray-100 gap-5 p-4" style="background-size: cover;">
    <div class="relative w-full max-w-[4000px] flex flex-col md:flex-row md:justify-evenly items-center gap-6">
        <a href="/setting" target="_blank" class="absolute top-2 right-2 p-2 text-gray-600 hover:text-gray-800 z-20 bg-white rounded">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37 1 .608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
        </a>
        <div class="text-center w-full max-w-[300px] md:max-w-[350px]">
            <img src="./img/vqmm.png" alt="" class="w-full max-w-[300px] md:max-w-[400px] mx-auto">
            <div class="relative w-full aspect-square mx-auto mt-3">
                <div class="absolute -top-5 left-1/2 -translate-x-1/2 border-l-[15px] border-r-[15px] border-t-[30px] border-l-transparent border-r-transparent border-t-yellow-400 z-10"></div>
                <div id="wheel" class="w-full h-full rounded-full overflow-hidden transition-transform duration-[4000ms] ease-out shadow-xl">
                    <canvas id="wheelCanvas" class="w-full h-full"></canvas>
                </div>
            </div>
            <button id="spinButton" onclick="spin()" class="mt-4 px-5 py-2 text-white bg-green-500 hover:bg-green-600 rounded-md" disabled>Quay</button>
            <div id="result" class="mt-2 text-xl text-white font-semibold text-shadow"></div>
        </div>
        <div class="flex flex-col gap-4 w-full max-w-[350px] md:max-w-md">
            <div class="bg-white p-4 rounded-lg shadow-xl w-full">
                <h3 class="text-xl font-semibold mb-2">Lịch sử quay</h3>
                <button onclick="clearHistory()" class="mb-3 px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded">Xóa lịch sử</button>
                <ul id="historyList" class="list-none space-y-2 text-gray-700 text-left max-h-[200px] overflow-y-auto"></ul>
            </div>
        </div>
    </div>

    <!-- Modal hiển thị kết quả với Tailwind -->
    <div id="resultModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center" onclick="closeModal(event)">
        <div id="modalContent" class="bg-white p-5 rounded-lg text-center shadow-lg max-w-sm w-[90%]">
            <h2 class="text-2xl font-bold mb-4">Kết quả trúng thưởng</h2>
            <p id="modalResult" class="text-xl"></p>
            <!-- Bạn có thể thêm hình ảnh vào đây sau này -->
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
        let isSpinning = false;
        let history = [];
        const canvas = document.getElementById('wheelCanvas');
        const ctx = canvas.getContext('2d');
        const spinButton = document.getElementById('spinButton');
        const API_BASE_URL = 'http://localhost:8000/api';

        const updateCanvasSize = () => {
            if (window.innerWidth >= 768) {
                canvas.width = 400;
                canvas.height = 400;
            } else {
                canvas.width = 300;
                canvas.height = 300;
            }
            drawWheel();
        };

        async function fetchWheels() {
            try {
                const response = await fetch(`${API_BASE_URL}/wheels`);
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
                }
                currentWheelId = wheels.some(w => w.id === data.current_wheel_id) ? data.current_wheel_id : 'wheel-0';
                items = wheels.find(w => w.id === currentWheelId)?.items || defaultWheel.items;
                drawWheel();
                spinButton.disabled = false;
            } catch (error) {
                console.error('Error fetching wheels:', error);
                items = defaultWheel.items;
                drawWheel();
                spinButton.disabled = true;
            }
        }

        function drawWheel() {
            const radius = canvas.width / 2;
            const anglePerItem = 2 * Math.PI / items.length;
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Vẽ các phần của vòng quay với màu xen kẽ cam và đen
            items.forEach((item, index) => {
                const startAngle = index * anglePerItem;
                const endAngle = (index + 1) * anglePerItem;
                const color = index % 2 === 0 ? '#f97316' : '#000000'; // Cam và đen xen kẽ

                ctx.beginPath();
                ctx.moveTo(radius, radius);
                ctx.arc(radius, radius, radius, startAngle, endAngle);
                ctx.fillStyle = color;
                ctx.fill();

                item.angleStart = startAngle * 180 / Math.PI;
                item.angleEnd = endAngle * 180 / Math.PI;
            });

            // Vẽ đường viền trắng giữa các phần
            ctx.lineWidth = 5;
            ctx.strokeStyle = '#f00';
            items.forEach((_, index) => {
                const angle = index * anglePerItem;
                ctx.beginPath();
                ctx.moveTo(radius, radius);
                ctx.lineTo(radius + radius * Math.cos(angle), radius + radius * Math.sin(angle));
                ctx.stroke();
            });

            // Vẽ viền ngoài vòng quay
            ctx.beginPath();
            ctx.arc(radius, radius, radius - ctx.lineWidth / 2, 0, 2 * Math.PI);
            ctx.stroke();

            // Vẽ chữ với khả năng xuống dòng
            items.forEach((item, index) => {
                const startAngle = index * anglePerItem;
                ctx.save();
                ctx.translate(radius, radius);
                ctx.rotate(startAngle + anglePerItem / 2);
                ctx.fillStyle = '#fff';
                ctx.font = `bold ${canvas.width >= 400 ? '16px' : '12px'} Arial`;
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';

                // Chia nhỏ văn bản nếu quá dài
                const maxWidth = radius * 0.7;
                const words = item.name.split(' ');
                let line = '';
                let lines = [];
                for (let word of words) {
                    const testLine = line + (line ? ' ' : '') + word;
                    const metrics = ctx.measureText(testLine);
                    if (metrics.width > maxWidth) {
                        lines.push(line);
                        line = word;
                    } else {
                        line = testLine;
                    }
                }
                if (line) lines.push(line);

                // Vẽ từng dòng chữ gần tâm hơn
                const lineHeight = canvas.width >= 400 ? 20 : 16;
                const yOffset = -(lines.length - 1) * lineHeight / 2;
                lines.forEach((line, i) => {
                    ctx.fillText(line, radius * 0.5, yOffset + i * lineHeight);
                });

                ctx.restore();
            });

            // Vẽ hình tròn màu vàng ở chính giữa
            ctx.beginPath();
            ctx.arc(radius, radius, radius * 0.15, 0, 2 * Math.PI); // Bán kính 15% của vòng quay
            ctx.fillStyle = '#16a149';
            ctx.fill();

            // Vẽ hình tròn màu xanh ở mép mỗi mục
            items.forEach((_, index) => {
                const middleAngle = index * anglePerItem + anglePerItem / 2;
                const greenDotRadius = radius * 0.05;
                const dotDistanceFromCenter = radius * 0.9;
                const x = radius + dotDistanceFromCenter * Math.cos(middleAngle);
                const y = radius + dotDistanceFromCenter * Math.sin(middleAngle);
                ctx.beginPath();
                ctx.arc(x, y, greenDotRadius, 0, 2 * Math.PI);
                ctx.fillStyle = '#e8c01e';
                ctx.fill();
            });
        }

        async function spin() {
            if (isSpinning || spinButton.disabled || document.getElementById('result').textContent === 'Đang quay ...') return;
            isSpinning = true;
            spinButton.disabled = true;

            const totalWeight = items.reduce((sum, item) => sum + item.weight, 0);
            const random = Math.random() * totalWeight;
            let cumulative = 0;
            const winner = items.find(item => {
                cumulative += item.weight;
                return random <= cumulative;
            });

            const wheel = document.getElementById('wheel');
            const winnerMiddleAngle = (winner.angleStart + winner.angleEnd) / 2;
            const targetAngle = -90 - winnerMiddleAngle;
            const randomSpin = 720 * 5 + targetAngle;

            wheel.style.transform = `rotate(${randomSpin}deg)`;
            document.getElementById('result').textContent = 'Đang quay ...';
            setTimeout(async () => {
                isSpinning = false;
                document.getElementById('result').textContent = `Kết quả: ${winner.name}`;
                showModal(winner.name);
                await updateHistory(winner.name);
                await fetchWheels();
                wheel.style.transition = 'none';
                wheel.style.transform = `rotate(${targetAngle}deg)`;
                setTimeout(() => {
                    wheel.style.transition = 'transform 4s ease-out';
                }, 50);
            }, 4000);
        }

        async function updateHistory(result) {
            try {
                const response = await fetch(`${API_BASE_URL}/history`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ result })
                });
                if (!response.ok) throw new Error('Không thể lưu lịch sử');
                await fetchHistory();
            } catch (error) {
                console.error('Error saving history:', error);
            }
        }

        async function fetchHistory() {
            try {
                const response = await fetch(`${API_BASE_URL}/history`);
                if (!response.ok) throw new Error('Không thể tải lịch sử');
                history = await response.json();
                const list = document.getElementById('historyList');
                list.innerHTML = history.map(item => `<li class="list-none">${new Date(item.spun_at).toLocaleString()} - ${item.result}</li>`).join('');
            } catch (error) {
                console.error('Error fetching history:', error);
            }
        }

        async function clearHistory() {
            try {
                const response = await fetch(`${API_BASE_URL}/history`, {
                    method: 'DELETE'
                });
                if (!response.ok) throw new Error('Không thể xóa lịch sử');
                history = [];
                document.getElementById('historyList').innerHTML = '';
            } catch (error) {
                console.error('Error clearing history:', error);
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

        // Hàm hiển thị modal
        function showModal(result) {
            const modal = document.getElementById('resultModal');
            const modalResult = document.getElementById('modalResult');
            modalResult.textContent = result;
            modal.classList.remove('hidden');
        }

        // Hàm đóng modal khi bấm bên ngoài
        function closeModal(event) {
            const modal = document.getElementById('resultModal');
            const modalContent = document.getElementById('modalContent');
            if (event.target === modal) {
                modal.classList.add('hidden');
            }
        }

        window.onload = async () => {
            await fetchBackground();
            await fetchWheels();
            await fetchHistory();
            updateCanvasSize();
            window.addEventListener('resize', updateCanvasSize);
        };
    </script>
</body>
</html>