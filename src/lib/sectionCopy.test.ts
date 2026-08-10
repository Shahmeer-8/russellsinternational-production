import { describe, expect, it } from "vitest";
import { readFileSync, readdirSync } from "node:fs";
import { join } from "node:path";

/**
 * Each component keeps its old wording as the fallback in copy("field", "text"),
 * and the backend seeder inserts the same wording into page_sections. If the two
 * drift, seeding silently rewrites the live site — which nearly happened with the
 * Study Abroad heading.
 *
 * This reads both sides and compares them, so the mismatch fails a test instead of
 * reaching production.
 */

const COMPONENTS = join(process.cwd(), "src", "components");
const SEEDER = join(
  process.cwd(),
  "russellsinternational-api",
  "database",
  "seeders",
  "SectionHeadingSeeder.php",
);

type Fallback = { page: string; key: string; field: string; text: string };

function collectFallbacks(): Fallback[] {
  const found: Fallback[] = [];

  for (const file of readdirSync(COMPONENTS).filter((f) => f.endsWith(".tsx") && !f.endsWith(".test.tsx"))) {
    const src = readFileSync(join(COMPONENTS, file), "utf8");
    const bind = src.match(/useSectionCopy\(\s*"([^"]+)"\s*,\s*"([^"]+)"\s*\)/);
    if (!bind) continue;

    const [, page, key] = bind;
    // copy("field", "the text") — text may contain escaped quotes.
    for (const m of src.matchAll(/copy\(\s*"(\w+)"\s*,\s*"((?:[^"\\]|\\.)*)"\s*\)/g)) {
      found.push({ page, key, field: m[1], text: m[2].replace(/\\"/g, '"').replace(/\\\\/g, "\\") });
    }
  }

  return found;
}

function seededText(php: string, page: string, key: string, field: string): string | null {
  // Each entry is a PHP array literal; find the block for this page/key pair.
  const blocks = php.split(/\[\s*\n\s*'page_slug'/).slice(1);

  for (const block of blocks) {
    const p = block.match(/^\s*=>\s*'([^']*)'/);
    const k = block.match(/'section_key'\s*=>\s*'([^']*)'/);
    if (!p || !k || p[1] !== page || k[1] !== key) continue;

    const single = block.match(new RegExp(`'${field}'\\s*=>\\s*'((?:[^'\\\\]|\\\\.)*)'`));
    if (single) return single[1].replace(/\\'/g, "'").replace(/\\\\/g, "\\");

    const double = block.match(new RegExp(`'${field}'\\s*=>\\s*"((?:[^"\\\\]|\\\\.)*)"`));
    if (double) return double[1].replace(/\\"/g, '"').replace(/\\\\/g, "\\");

    return null;
  }

  return null;
}

describe("section copy stays in sync with the seeder", () => {
  const fallbacks = collectFallbacks();
  const php = readFileSync(SEEDER, "utf8");

  it("finds the wired components", () => {
    expect(fallbacks.length).toBeGreaterThanOrEqual(20);
  });

  it("every component fallback matches the seeded text exactly", () => {
    const drift: string[] = [];

    for (const f of fallbacks) {
      const seeded = seededText(php, f.page, f.key, f.field);

      if (seeded === null) {
        drift.push(`${f.page}/${f.key}.${f.field}: not seeded (component says "${f.text.slice(0, 40)}")`);
        continue;
      }

      if (seeded !== f.text) {
        drift.push(`${f.page}/${f.key}.${f.field}:\n    component: "${f.text}"\n    seeder:    "${seeded}"`);
      }
    }

    expect(drift, `Seeding would change the live site:\n  ${drift.join("\n  ")}`).toEqual([]);
  });
});
