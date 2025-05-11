import json

# Daftar ID dari gambar Anda
ids = [
    "0120.01173", "0309.01022", "0220.01176", "0220.01175", "0415.01125",
    "0109.01008", "0313.01026", "0110.01016", "1218.01115", "111001042",
    "1014.01113", "0109.01043", "0919.01135", "1213.01086", "0819.01129",
    "0817.01168", "1022.02172", "0316.01139", "1220.01204", "0518.01186",
    "1112.01068", "0212.01069", "0119.01105", "1122.02174", "0220.01141",
    "0319.01114", "102.202.173", "0121.01220", "0918.01194", "0422.01253",
    "0813.01059", "0111.01058", "0419.01119", "0316.01140", "0113.02099",
    "0613.02097", "0124.02277", "0816.02139", "0122.00046", "0215.02117",
    "0520.02223", "0220.02192", "1212.02100", "0112.02082", "0109.02086",
    "0518.02163", "0610.02084", "1122.02238", "0309.02117", "0121.02229",
    "0222.01241", "0222.00060", "0119.01102", "0519.01124", "0715.02124"
]

# Asumsikan ini data JSON awal yang sudah Anda punya
with open("user.json", "r", encoding="utf-8") as f:
    data = json.load(f)

# Cek panjangnya sama
if len(data) != len(ids):
    raise ValueError(f"Jumlah ID ({len(ids)}) dan jumlah data ({len(data)}) tidak sama!")

# Tambahkan ID ke setiap entri
for entry, id_value in zip(data, ids):
    entry["id"] = id_value

# Simpan ke file baru
with open("data_dengan_id.json", "w", encoding="utf-8") as f:
    json.dump(data, f, indent=2, ensure_ascii=False)
