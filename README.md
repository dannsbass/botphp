# botphp
Bot Telegram sederhana dengan bahasa PHP

# Syarat pakai
1. Di komputer harus sudah terinstal PHP 5.4 ke atas

# Cara pakai
1. Buat file baru bernama `data.txt` di folder yang sama dengan file ini.
2. Masukkan data berikut:

DATA BOT

token = ... (token bot anda)

username = ... (username bot anda)

# Respon untuk pesan teks
Untuk membuat respon atas teks kiriman user, gunakan format berikut:

teks user -> respon bot [tombol]

CONTOH

/start  -> Selamat datang. Untuk memilih menu ketik /menu atau /link

/menu -> Silahkan pilih\menu berikut [satu] [dua] # [tiga] [empat] # [lima]

/link -> Silahkan pilih link berikut [Google|https://www.google.com] [dua|2] # [tiga|3] [empat|4] # [lima|5]

lima -> Anda menulis lima

# Respon untuk Callback Query
Untuk membuat respon atas tombol yang ditekan oleh user, gunakan format berikut:

data tombol => respon

CONTOH

2 => Anda memilih dua [Google|https://www.google.com] [dua|2] # [tiga|3] [empat|4] # [lima|5]

3 => Anda memilih tiga [Danns Net | https://dannsnet.wordpress.com] [Tutorial 2|https://tutorial2.com] # [Tutorial 3 | https://tutorial3.com]

4 => Anda memilih empat[Google|https://www.google.com] [dua|2] # [tiga|3] [empat|4] # [lima|5]

5 => Anda memilih lima [satu] [dua] # [tiga] [empat] # [lima]

# Reply keyboard

Untuk membuat reply keyboard, cukup menulis teksnya di antara dua kurung kotak, contoh: [HOME], [ABOUT], [info]

# Inline keyboard

Untuk membuat inline keyboard, cukup menulisnya di dalam kurung kotak dengan pemisah berupa tanda pipa, contoh: [Google|https://www.google.com]

# Baris baru

Untuk respon teks, gunakan tanda backslash untuk baris baru, contoh: Ini baris kesatu\Ini baris kedua

Untuk reply keyboard dan inline keyboard, gunakan tanda pagar untuk baris bari, contoh: [info] [tentang] # [produk] [reseller]
