import { createRoot } from '@wordpress/element';
import App from './App';

const rootElement = document.getElementById('practicerx-root');

if (rootElement) {
    const root = createRoot(rootElement);
    root.render(<App />);
}
