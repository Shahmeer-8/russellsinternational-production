import { afterEach, describe, expect, it, vi } from "vitest";
import { render, screen } from "@testing-library/react";
import ResponsiveCardRow from "@/components/ResponsiveCardRow";

const mockIsMobile = vi.fn();

vi.mock("@/hooks/use-mobile", () => ({
  useIsMobile: () => mockIsMobile(),
}));

const items = ["A", "B", "C", "D", "E", "F"].map((label) => ({
  key: label,
  node: <div className="premium-card">{label}</div>,
}));

afterEach(() => vi.clearAllMocks());

describe("ResponsiveCardRow", () => {
  it("renders the plain grid on desktop so the existing layout is untouched", () => {
    mockIsMobile.mockReturnValue(false);

    const { container } = render(<ResponsiveCardRow items={items} />);

    expect(container.querySelector(".grid")).toBeTruthy();
    expect(container.querySelector("[data-slot=carousel], [role=region]")).toBeNull();
    expect(screen.getAllByText(/^[A-F]$/)).toHaveLength(6);
  });

  it("swipes horizontally on mobile so a long row costs one card of height", () => {
    mockIsMobile.mockReturnValue(true);

    const { container } = render(<ResponsiveCardRow items={items} />);

    expect(container.querySelector(".grid")).toBeNull();
    expect(container.querySelector(".overflow-hidden")).toBeTruthy();
    expect(screen.getAllByText(/^[A-F]$/)).toHaveLength(6);
  });

  it("keeps every card mounted on mobile so nothing is lost to the carousel", () => {
    mockIsMobile.mockReturnValue(true);

    render(<ResponsiveCardRow items={items} />);

    for (const label of ["A", "B", "C", "D", "E", "F"]) {
      expect(screen.getByText(label)).toBeTruthy();
    }
  });

  it("lets the caller keep its own desktop grid classes", () => {
    mockIsMobile.mockReturnValue(false);

    const { container } = render(
      <ResponsiveCardRow items={items} gridClassName="grid md:grid-cols-4 gap-2" />,
    );

    expect(container.querySelector(".md\\:grid-cols-4")).toBeTruthy();
  });

  it("renders nothing harmful for an empty list", () => {
    mockIsMobile.mockReturnValue(true);

    const { container } = render(<ResponsiveCardRow items={[]} />);

    expect(container.textContent).toBe("");
  });
});
