"""Download missing photos from the reviewed Wikimedia manifest."""
import json, time, urllib.request
from pathlib import Path
root = Path(__file__).resolve().parents[1]
manifest = json.loads((root / 'resources/data/product-images.json').read_text(encoding='utf-8'))
out = root / 'public/images/products'
out.mkdir(parents=True, exist_ok=True)
for name, photo in manifest.items():
    target = out / photo['file']
    if target.exists():
        continue
    request = urllib.request.Request(photo['download_url'], headers={'User-Agent': 'NutriTrace/1.0 educational catalogue'})
    with urllib.request.urlopen(request, timeout=60) as response:
        target.write_bytes(response.read())
    print(name, flush=True)
    time.sleep(2)
