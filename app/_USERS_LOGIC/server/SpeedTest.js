async function measurePing(iterations = 5) {
    const url = "/app/_USERS_LOGIC/server/mbps.dat?" + new Date().getTime(); // Tránh cache
    let pings = [];
    
    for (let i = 0; i < iterations; i++) {
        let start = performance.now();
        try {
            // Dùng HEAD request với timeout 2s
            await fetch(url, { method: "HEAD", cache: "no-store", signal: AbortSignal.timeout(2000) });
            let end = performance.now();
            pings.push(end - start);
        } catch {
            pings.push(1000); // Nếu lỗi, gán ping cao
        }
    }

    const avgPing = pings.reduce((sum, val) => sum + val, 0) / pings.length;
    const jitter = pings.reduce((acc, ping) => acc + Math.abs(ping - avgPing), 0) / pings.length;

    return { avgPing: avgPing.toFixed(2), jitter: jitter.toFixed(2) };
}

async function measureDownloadSpeed() {
    return new Promise((resolve) => {
        const fileUrl = "/app/_USERS_LOGIC/server/mbps.dat?" + new Date().getTime();
        let totalBytes = 0;
        let startTime = performance.now();
        let fetchCount = 0;
        let errorCount = 0;
        const maxTime = 10 * 1000; // 10 giây tối đa

        async function fetchLoop() {
            // Nếu đã chạy đủ 100 lần hoặc vượt quá 10s thì dừng lại
            if (fetchCount >= 100 || (performance.now() - startTime) > maxTime) {
                let elapsed = (performance.now() - startTime) / 1000;
                let speedMbps = (totalBytes * 8 / elapsed) / (1024 * 1024);
                return resolve(speedMbps.toFixed(2));
            }

            try {
                // Dùng fetch với timeout 5s
                let response = await fetch(fileUrl, { cache: "no-store", signal: AbortSignal.timeout(5000) });
                let reader = response.body.getReader();
                let done = false;

                while (!done) {
                    let { value, done: readerDone } = await reader.read();
                    if (value) totalBytes += value.byteLength;
                    done = readerDone;
                }
                errorCount = 0; // Reset lỗi nếu fetch thành công
            } catch (error) {
                console.error("Lỗi khi tải:", error);
                errorCount++;
                if (errorCount > 5) {
                    document.getElementById("downloadResult").textContent = "Lỗi mạng!";
                    return resolve("0");
                }
            }

            fetchCount++;
            let elapsed = (performance.now() - startTime) / 1000;
            let speedMbps = (totalBytes * 8 / elapsed) / (1024 * 1024);
            document.getElementById("downloadResult").textContent = speedMbps.toFixed(2) + " Mbps";

            setTimeout(fetchLoop, 100); // Lặp lại sau 100ms
        }

        fetchLoop();
    });
}

async function runNetworkTests() {
    const pingResultElem = document.getElementById("pingResult");
    const jitterResultElem = document.getElementById("jitterResult");
    const downloadResultElem = document.getElementById("downloadResult");

    // Đo ping & jitter
    const { avgPing, jitter } = await measurePing();
    pingResultElem.textContent = avgPing + " ms";
    jitterResultElem.textContent = jitter + " ms";

    // Đo download speed real-time
    downloadResultElem.textContent = "Đang đo...";
    const downloadSpeed = await measureDownloadSpeed();
    downloadResultElem.textContent = downloadSpeed + " Mbps";
}

window.addEventListener("load", runNetworkTests);

