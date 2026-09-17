#!/bin/bash
# ============================================================
# Script: copy-foto.sh
# Fungsi: Menyalin folder foto/ ke public/foto/
# Jalankan dari dalam folder backend/:
#   bash copy-foto.sh
# ============================================================

SOURCE="$(dirname "$0")/foto"
DEST="$(dirname "$0")/public/foto"

if [ ! -d "$SOURCE" ]; then
    echo "[ERROR] Folder sumber tidak ditemukan: $SOURCE"
    exit 1
fi

mkdir -p "$DEST"
cp -v "$SOURCE"/*.jpg "$DEST"/ 2>/dev/null
cp -v "$SOURCE"/*.jpeg "$DEST"/ 2>/dev/null
cp -v "$SOURCE"/*.png "$DEST"/ 2>/dev/null

echo ""
echo "[OK] Semua file gambar berhasil disalin ke: $DEST"
echo ""
ls -lh "$DEST"
