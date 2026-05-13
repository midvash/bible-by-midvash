#!/usr/bin/env tsx
/**
 * Empacota o plugin "Bible by Midvash" num ZIP pronto pra distribuir.
 *
 * - Lê a versão do header `Version:` em `bible-by-midvash.php` (raiz do repo).
 * - Inclui só o que vai pro WordPress (php, includes, assets, languages, vendor, readme.txt).
 * - Estrutura interna do zip: `bible-by-midvash/` (slug oficial — WP exige).
 * - Saída: `dist/bible-by-midvash-{version}.zip`.
 *
 * Usa o `zip` do macOS/linux. Sem deps extras.
 */

import { execSync } from 'node:child_process';
import { existsSync, mkdirSync, readFileSync, rmSync, cpSync } from 'node:fs';
import { join, resolve } from 'node:path';
import { tmpdir } from 'node:os';

const ROOT = resolve(__dirname, '..');
const PLUGIN_PHP = join(ROOT, 'bible-by-midvash.php');
const DIST_DIR = join(ROOT, 'dist');
const SLUG = 'bible-by-midvash';

function readVersion(): string {
  if (!existsSync(PLUGIN_PHP)) {
    throw new Error(`Plugin PHP not found at ${PLUGIN_PHP}`);
  }
  const content = readFileSync(PLUGIN_PHP, 'utf8');
  const match = content.match(/^\s*\*\s*Version:\s*([0-9]+\.[0-9]+\.[0-9]+)/m);
  if (!match) {
    throw new Error('Could not parse `Version:` header in bible-by-midvash.php');
  }
  return match[1];
}

function main() {
  const version = readVersion();
  const stagingRoot = join(tmpdir(), `midvash-wp-build-${Date.now()}`);
  const stagingPlugin = join(stagingRoot, SLUG);
  const outFile = join(DIST_DIR, `${SLUG}-${version}.zip`);

  mkdirSync(DIST_DIR, { recursive: true });
  mkdirSync(stagingPlugin, { recursive: true });

  // Files to include — plugin code only, no repo metadata (README.md, LICENSE, scripts, .github)
  const includePaths = ['bible-by-midvash.php', 'readme.txt', 'includes', 'assets', 'languages', 'vendor'];
  for (const p of includePaths) {
    const src = join(ROOT, p);
    if (!existsSync(src)) continue;
    cpSync(src, join(stagingPlugin, p), { recursive: true });
  }

  if (existsSync(outFile)) rmSync(outFile);
  execSync(`cd "${stagingRoot}" && zip -rq "${outFile}" "${SLUG}"`, { stdio: 'inherit' });
  rmSync(stagingRoot, { recursive: true, force: true });

  console.log(`✓ ${outFile} (v${version})`);
}

main();
