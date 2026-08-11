import { describe, expect, it, vi } from "vitest";
import { render, screen, waitFor } from "@testing-library/react";
import StatsStrip from "@/components/StatsStrip";

const mockUseStats = vi.fn();

vi.mock("@/hooks/api", () => ({
  useStats: () => mockUseStats(),
}));

/**
 * useScrollReveal's IntersectionObserver effect runs exactly once (its dependency
 * never changes), and it only observes an element that already exists when the
 * effect first fires. If the component returns null while data is loading, the
 * ref never mounts on that first pass, the effect finds nothing to observe, and
 * — because it never runs again — the section stays invisible forever once data
 * arrives. This is what produced a blank gap on the live site: stats and
 * services were in the DOM at full height but permanently opacity: 0.
 */
describe("StatsStrip scroll reveal", () => {
  it("mounts its wrapper while still loading, so the reveal observer has something to observe", () => {
    mockUseStats.mockReturnValue({ data: undefined, isLoading: true });

    const { container } = render(<StatsStrip />);

    expect(container.querySelector("section")).toBeTruthy();
  });

  it("becomes visible once data arrives after a loading render", async () => {
    mockUseStats.mockReturnValue({ data: undefined, isLoading: true });
    const { container, rerender } = render(<StatsStrip />);

    mockUseStats.mockReturnValue({
      data: { data: [{ id: 1, value: "5,000+", label: "Students Placed", icon_name: "Users" }] },
      isLoading: false,
    });
    rerender(<StatsStrip />);

    await waitFor(() => {
      const wrapper = container.querySelector("section > div");
      expect(wrapper?.className).not.toContain("opacity-0");
    });
    expect(screen.getByText("Students Placed")).toBeTruthy();
  });

  it("renders nothing once loading finishes with no stats configured", () => {
    mockUseStats.mockReturnValue({ data: { data: [] }, isLoading: false });

    const { container } = render(<StatsStrip />);

    expect(container.querySelector("section")).toBeNull();
  });
});
