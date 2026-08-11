import { useCallback, useRef, useState } from "react";

/**
 * Several components on this site render a loading skeleton on their first pass
 * and only attach `ref` once real content mounts on a later render (About.tsx's
 * Campus Life section, LanguagesSection, StatsStrip, ServicesSection). A
 * `useRef` + `useEffect` implementation observes whatever `ref.current` is when
 * the effect first runs; if that first render never attached the ref, the effect
 * finds nothing to watch, and — because its dependency array never changes —
 * it never runs again. The section then stays invisible forever once content
 * finally does mount.
 *
 * A callback ref sidesteps this: React invokes it whenever the DOM node backing
 * that ref prop is attached, on whatever render that happens to be, so a section
 * that only gains its ref on its second render still gets observed correctly.
 */
export function useScrollReveal(threshold = 0.15) {
  const [visible, setVisible] = useState(false);
  const observerRef = useRef<IntersectionObserver | null>(null);

  const ref = useCallback(
    (node: Element | null) => {
      observerRef.current?.disconnect();
      observerRef.current = null;

      if (!node) {
        return;
      }

      const observer = new IntersectionObserver(
        ([entry]) => {
          if (entry.isIntersecting) {
            setVisible(true);
            observer.disconnect();
          }
        },
        { threshold },
      );

      observer.observe(node);
      observerRef.current = observer;
    },
    [threshold],
  );

  return { ref, visible };
}
