import fs from 'node:fs';
import path from 'node:path';

const targets = [
    'public/build',
    'node_modules/.vite-temp',
];

function ensureWritableDirectory(targetPath) {
    if (!fs.existsSync(targetPath)) {
        fs.mkdirSync(targetPath, { recursive: true });
    }

    const stack = [targetPath];

    while (stack.length > 0) {
        const currentPath = stack.pop();

        try {
            fs.chmodSync(currentPath, 0o775);
        } catch {
            // Ignore permission errors here; Vite will surface anything still blocked.
        }

        for (const entry of fs.readdirSync(currentPath, { withFileTypes: true })) {
            const entryPath = path.join(currentPath, entry.name);
            if (entry.isDirectory()) {
                stack.push(entryPath);
            }
        }
    }
}

for (const target of targets) {
    ensureWritableDirectory(target);
}
