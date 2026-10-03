// Include template images in the Vite manifest for Vite::asset().
import.meta.glob('../images/**', { eager: true, query: '?url', import: 'default' });
