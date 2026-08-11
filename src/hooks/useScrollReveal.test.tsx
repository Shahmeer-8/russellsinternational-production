import { describe, expect, it } from "vitest";
import { render, waitFor } from "@testing-library/react";
import { useScrollReveal } from "@/hooks/useScrollReveal";

/**
 * Real components in this codebase render a loading skeleton on the first pass
 * and only attach `ref` once real content mounts on a later render — About.tsx's
 * Campus Life section, LanguagesSection, StatsStrip and ServicesSection all do
 * this. If useScrollReveal only observes whatever `ref.current` is when its
 * effect first runs, and that first render never attached the ref, the section
 * stays invisible forever once content finally does mount: the effect's
 * dependency array never changes, so it never gets a second chance to observe.
 *
 * This is the "blank gap on the homepage" bug reported in production. The test
 * mirrors the real shape (a component that mounts the ref on its second render,
 * not its first) rather than mounting the ref immediately, which would not
 * reproduce the defect.
 */
function LateRefMount({ showRef }: { showRef: boolean }) {
  const { ref, visible } = useScrollReveal();

  return (
    <div data-testid="probe" data-visible={visible}>
      {showRef ? <div ref={ref} data-testid="target" /> : <div data-testid="skeleton" />}
    </div>
  );
}

describe("useScrollReveal", () => {
  it("still reveals when the ref only mounts on a later render, not the first", async () => {
    const { rerender, getByTestId } = render(<LateRefMount showRef={false} />);

    expect(getByTestId("skeleton")).toBeTruthy();
    expect(getByTestId("probe").dataset.visible).toBe("false");

    rerender(<LateRefMount showRef={true} />);

    await waitFor(() => {
      expect(getByTestId("probe").dataset.visible).toBe("true");
    });
  });

  it("reveals immediately when the ref is present from the first render", async () => {
    function Immediate() {
      const { ref, visible } = useScrollReveal();
      return <div ref={ref} data-testid="probe" data-visible={visible} />;
    }

    const { getByTestId } = render(<Immediate />);

    await waitFor(() => {
      expect(getByTestId("probe").dataset.visible).toBe("true");
    });
  });

  it("stops observing once revealed, so scrolling away does not hide it again", async () => {
    function Once() {
      const { ref, visible } = useScrollReveal();
      return <div ref={ref} data-testid="probe" data-visible={visible} />;
    }

    const { getByTestId, rerender } = render(<Once />);

    await waitFor(() => expect(getByTestId("probe").dataset.visible).toBe("true"));

    rerender(<Once />);
    expect(getByTestId("probe").dataset.visible).toBe("true");
  });
});
