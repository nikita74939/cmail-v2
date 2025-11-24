# C-Mail

C-Mail adalah aplikasi web secure messaging yang memanfaatkan
**kriptografi modern** (Argon2i, ECC, dan Super Encryption) serta
**steganografi DCT + LSB** untuk mengamankan pesan dan file. Dibangun
dengan Laravel dan UI minimalis, C-Mail berfungsi sebagai platform
ringan untuk belajar, menguji, dan menerapkan teknik enkripsi serta
penyembunyian pesan secara praktis.

------------------------------------------------------------------------

## Fitur Utama

-   **Argon2i Secure Authentication** --- Hashing password yang kuat dan
    modern.
-   **ECC (Elliptic Curve Cryptography)** --- Enkripsi asymmetric untuk
    pesan dan file.
-   **Super Encryption (Route Cipher + ECC)** --- Lapisan keamanan ganda
    untuk teks.
-   **Steganografi DCT + LSB** --- Menyisipkan pesan rahasia ke dalam
    gambar.
-   **Secret Message Menu** --- Fitur khusus untuk upload gambar berisi
    pesan tersembunyi.
-   **UI Modern & Ringan** --- Tampilan minimalis berbasis HTML/CSS/JS.

------------------------------------------------------------------------

## Tech Stack

-   **Backend:** Laravel (PHP)
-   **Crypto Services:** ECC, Route Cipher, Super Encryption, DCT-LSB
    Steganography
-   **Database:** MySQL
-   **Frontend:** HTML, CSS, JavaScript, Tailwind

------------------------------------------------------------------------

## Instalasi

1.  Clone repository:

    ``` bash
    git clone https://github.com/yourusername/cmail.git
    cd cmail
    ```

2.  Install dependencies:

    ``` bash
    composer install
    npm install && npm run build
    ```

3.  Copy file environment:

    ``` bash
    cp .env.example .env
    ```

4.  Generate app key:

    ``` bash
    php artisan key:generate
    ```

5.  Konfigurasi database pada `.env`

6.  Jalankan migrasi:

    ``` bash
    php artisan migrate
    ```

7.  Jalankan server:

    ``` bash
    php artisan serve
    ```

------------------------------------------------------------------------

## Fitur Kriptografi

  
  1. **ECC Encryption**        Enkripsi public-key untuk teks dan file.

  2. **Route Cipher**          Pola scrambling sebelum enkripsi ECC.

  3. **Super Encryption**      Kombinasi Route Cipher → ECC untuk keamanan
                               ekstra.

  4. **Stegano DCT+LSB**       Menyembunyikan pesan dalam file gambar.

------------------------------------------------------------------------

## Struktur Folder Penting

    app/Services/Crypto/
     ├── ECCService.php
     ├── RouteCipherService.php
     ├── SuperEncryptionService.php
     └── SteganoService.php

