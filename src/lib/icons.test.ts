import { describe, expect, it } from "vitest";
import { Award, Code, Globe } from "lucide-react";
import { DEFAULT_ICON, ICON_MAP, resolveIcon } from "@/lib/icons";

describe("resolveIcon", () => {
  it("returns the mapped icon for a known name", () => {
    expect(resolveIcon("Award")).toBe(Award);
  });

  it("falls back to the default icon for an unknown name", () => {
    expect(resolveIcon("NotARealIcon")).toBe(DEFAULT_ICON);
    expect(DEFAULT_ICON).toBe(Globe);
  });

  it("falls back for null, undefined and empty names", () => {
    expect(resolveIcon(null)).toBe(DEFAULT_ICON);
    expect(resolveIcon(undefined)).toBe(DEFAULT_ICON);
    expect(resolveIcon("")).toBe(DEFAULT_ICON);
  });

  it("honours a caller-supplied fallback so existing components keep their icon", () => {
    expect(resolveIcon("NotARealIcon", Code)).toBe(Code);
    expect(resolveIcon(null, Award)).toBe(Award);
  });

  it("exposes every icon the admin can pick", () => {
    for (const name of ["Award", "BookOpenText", "Code", "Globe", "Languages", "MessageCircle", "ScrollText"]) {
      expect(ICON_MAP[name], `${name} missing from ICON_MAP`).toBeTruthy();
    }
  });
});
