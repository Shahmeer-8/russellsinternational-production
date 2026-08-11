import { describe, expect, it, vi } from "vitest";
import { render, screen, waitFor } from "@testing-library/react";
import ServicesSection from "@/components/ServicesSection";

const mockUseServices = vi.fn();

vi.mock("@/hooks/api", () => ({
  useServices: () => mockUseServices(),
}));

vi.mock("@/hooks/useSectionCopy", () => ({
  useSectionCopy: () => (_field: string, fallback: string) => fallback,
}));

const service = {
  id: 1,
  icon_name: "Laptop",
  title: "IT & Skill Training",
  description: "Industry-certified programs.",
  details: "Details.",
  color_class: "bg-blue-50 text-blue-600",
  key_benefits: [],
};

/**
 * Same defect as StatsStrip: returning null while loading skips mounting the ref,
 * and useScrollReveal's one-shot observer effect never gets a second chance once
 * data arrives — the section renders at full height but stays invisible.
 */
describe("ServicesSection scroll reveal", () => {
  it("mounts its wrapper while still loading", () => {
    mockUseServices.mockReturnValue({ data: undefined, isLoading: true });

    const { container } = render(<ServicesSection />);

    expect(container.querySelector("section")).toBeTruthy();
  });

  it("becomes visible once data arrives after a loading render", async () => {
    mockUseServices.mockReturnValue({ data: undefined, isLoading: true });
    const { container, rerender } = render(<ServicesSection />);

    mockUseServices.mockReturnValue({ data: { data: [service] }, isLoading: false });
    rerender(<ServicesSection />);

    await waitFor(() => {
      const wrapper = container.querySelector("section > div");
      expect(wrapper?.className).not.toContain("opacity-0");
    });
    expect(screen.getByText("IT & Skill Training")).toBeTruthy();
  });

  it("renders nothing once loading finishes with no services configured", () => {
    mockUseServices.mockReturnValue({ data: { data: [] }, isLoading: false });

    const { container } = render(<ServicesSection />);

    expect(container.querySelector("section")).toBeNull();
  });
});
