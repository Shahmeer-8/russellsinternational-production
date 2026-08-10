import { describe, expect, it, vi } from "vitest";
import { render, screen } from "@testing-library/react";
import LanguagesSection from "@/components/LanguagesSection";
import type { LanguageSection } from "@/types/api";

const mockUseLanguageSections = vi.fn();

vi.mock("@/hooks/api", () => ({
  useLanguageSections: () => mockUseLanguageSections(),
}));

vi.mock("@/hooks/useScrollReveal", () => ({
  useScrollReveal: () => ({ ref: { current: null }, visible: true }),
}));

function section(overrides: Partial<LanguageSection> = {}): LanguageSection {
  return {
    id: 1,
    slug: "english",
    label: "English Tests",
    short_label: "English",
    tab_label: "English",
    heading: "English Test Preparation",
    subtitle: "IELTS, PTE and more.",
    icon_name: "Languages",
    color_class: "bg-blue-50 text-blue-600",
    sort_order: 1,
    programs: [
      {
        id: 10,
        title: "IELTS Preparation",
        duration: "8 Weeks",
        badge: "Most Popular",
        description: "Complete coaching.",
        benefits: ["Band score strategy"],
        color_class: "bg-blue-50 text-blue-600",
        icon_name: null,
        image_url: null,
      },
    ],
    ...overrides,
  };
}

describe("LanguagesSection", () => {
  it("renders one tab per section from the API", () => {
    mockUseLanguageSections.mockReturnValue({
      data: {
        data: [
          section(),
          section({
            id: 2,
            slug: "arabic",
            label: "Arabic Tests",
            tab_label: "Arabic",
            heading: "Arabic Language & Exams",
          }),
        ],
      },
      isLoading: false,
    });

    render(<LanguagesSection />);

    const tabs = screen.getAllByRole("tab");
    expect(tabs).toHaveLength(2);
    expect(tabs[0].textContent).toContain("English");
    expect(tabs[1].textContent).toContain("Arabic");
  });

  it("shows the active section's heading, subtitle and cards", () => {
    mockUseLanguageSections.mockReturnValue({ data: { data: [section()] }, isLoading: false });

    render(<LanguagesSection />);

    expect(screen.getByText("English Test Preparation")).toBeTruthy();
    expect(screen.getByText("IELTS, PTE and more.")).toBeTruthy();
    expect(screen.getByText("IELTS Preparation")).toBeTruthy();
  });

  it("marks the first section as the selected tab", () => {
    mockUseLanguageSections.mockReturnValue({
      data: { data: [section(), section({ id: 2, slug: "arabic", label: "Arabic Tests", tab_label: "Arabic" })] },
      isLoading: false,
    });

    render(<LanguagesSection />);

    const tabs = screen.getAllByRole("tab");
    expect(tabs[0].getAttribute("aria-selected")).toBe("true");
    expect(tabs[1].getAttribute("aria-selected")).toBe("false");
  });

  it("renders nothing when there are no sections instead of an empty shell", () => {
    mockUseLanguageSections.mockReturnValue({ data: { data: [] }, isLoading: false });

    const { container } = render(<LanguagesSection />);

    expect(container.querySelector("section")).toBeNull();
  });

  it("does not fall back to hardcoded demo programs", () => {
    mockUseLanguageSections.mockReturnValue({ data: { data: [] }, isLoading: false });

    render(<LanguagesSection />);

    expect(screen.queryByText("PTE Academic")).toBeNull();
    expect(screen.queryByText("Goethe A1-B2")).toBeNull();
    expect(screen.queryByText("TOPIK Preparation")).toBeNull();
  });

  it("survives an unknown icon name rather than crashing", () => {
    mockUseLanguageSections.mockReturnValue({
      data: { data: [section({ icon_name: "NotARealIcon" })] },
      isLoading: false,
    });

    render(<LanguagesSection />);

    expect(screen.getByText("English Test Preparation")).toBeTruthy();
  });

  it("keeps the tab strip on one row so extra languages add no vertical height", () => {
    mockUseLanguageSections.mockReturnValue({
      data: {
        data: Array.from({ length: 7 }, (_, i) =>
          section({ id: i + 1, slug: `lang-${i}`, label: `Language ${i}`, tab_label: `L${i}` })),
      },
      isLoading: false,
    });

    render(<LanguagesSection />);

    const strip = screen.getByRole("tablist");
    expect(strip.className).toContain("overflow-x-auto");
    expect(screen.getAllByRole("tab")).toHaveLength(7);
  });

  it("shows a loading placeholder while fetching", () => {
    mockUseLanguageSections.mockReturnValue({ data: undefined, isLoading: true });

    const { container } = render(<LanguagesSection />);

    expect(container.querySelectorAll(".animate-pulse").length).toBeGreaterThan(0);
  });
});
