<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Progress Bar Example</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <h1>Dynamic Progress Bar Example</h1>
    <table style="width: 50%;">
        <tr>
            <td>
                <div class="progress">
                    <div id="progress-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0"
                        aria-valuemin="5" aria-valuemax="100">0%</div>
                </div>
                <div id="time-running"></div>
                <div id="clock"></div>
            </td>
        </tr>
    </table>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentTime = new Date();
        let startTime = new Date(currentTime); // Menjadi waktu saat ini
        let endTime = new Date(startTime.getTime() + 0.5 * 60000); // Waktu sekarang ditambah 1 menit

        function updateProgressBar() {
            let currentTime = new Date();
            let timeDiff = endTime - startTime; //selisih waktu
            let timeElapsed = currentTime - startTime;
            let percentageElapsed = (timeElapsed / timeDiff) * 100;

            document.getElementById('progress-bar').style.width = percentageElapsed + '%';
            document.getElementById('progress-bar').innerText = percentageElapsed.toFixed(2) + '%';

            // Tampilkan waktu yang sedang berjalan
            let timeRunning = Math.floor((currentTime - startTime) / 1000); // Konversi ke detik
            let hours = Math.floor(timeRunning / 3600);
            let minutes = Math.floor((timeRunning % 3600) / 60);
            let seconds = timeRunning % 60;
            let timeString =
                `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            document.getElementById('time-running').innerText = timeString;

            setTimeout(updateProgressBar, 1000); // Perbarui setiap 1 detik
        }

        updateProgressBar();

        //waktu saat ini
        function updateTime() {
            // Ambil waktu saat ini
            var now = new Date();

            // Ekstrak jam, menit, dan detik
            var hours = now.getHours();
            var minutes = now.getMinutes();
            var seconds = now.getSeconds();

            // Menambahkan nol di depan angka jika angkanya kurang dari 10
            hours = padZero(hours);
            minutes = padZero(minutes);
            seconds = padZero(seconds);

            // Format waktu menjadi jam:menit:detik
            var timeString = hours + ":" + minutes + ":" + seconds;

            // Update elemen HTML dengan waktu yang baru
            document.getElementById("clock").innerHTML = timeString;
        }

        function padZero(num) {
            if (num < 10) {
                return "0" + num;
            } else {
                return num;
            }
        }

        // Panggil updateTime setiap detik
        setInterval(updateTime, 1000);

        // Jalankan updateTime saat halaman dimuat pertama kali
        updateTime();

        // Fungsi untuk reload halaman setiap 30 detik
        function reloadPage() {
            location.reload();
        }

        // Jalankan fungsi reload setiap 30 detik
        setInterval(reloadPage, 30000);
    </script>
</body>

</html>
