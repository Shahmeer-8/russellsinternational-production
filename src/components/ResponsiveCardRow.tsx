import type { ReactNode } from "react";
import {
  Carousel,
  CarouselContent,
  CarouselItem,
} from "@/components/ui/carousel";
import { useIsMobile } from "@/hooks/use-mobile";

type Props = {
  /** One entry per card. Keys come from the caller's data, not the index. */
  items: Array<{ key: string | number; node: ReactNode }>;
  /** Grid classes used from the `sm` breakpoint up. Desktop is left untouched. */
  gridClassName?: string;
};

/**
 * Card rows stack into one tall column on phones — six cards became roughly six
 * screens of scrolling. On mobile this swipes them horizontally instead, so a row
 * costs one card of height however many cards it holds. From `sm` up it renders
 * the original grid, so desktop layout is unchanged.
 *
 * Peeking the next card (85% width) is deliberate: a full-width card gives no hint
 * that there is more to swipe.
 */
const ResponsiveCardRow = ({ items, gridClassName = "grid sm:grid-cols-2 lg:grid-cols-3 gap-6" }: Props) => {
  const isMobile = useIsMobile();

  if (!isMobile) {
    return <div className={gridClassName}>{items.map((item) => <div key={item.key}>{item.node}</div>)}</div>;
  }

  return (
    <Carousel opts={{ align: "start", containScroll: "trimSnaps" }} className="-mx-4 px-4">
      <CarouselContent className="-ml-3">
        {items.map((item) => (
          <CarouselItem key={item.key} className="pl-3 basis-[85%]">
            {item.node}
          </CarouselItem>
        ))}
      </CarouselContent>
    </Carousel>
  );
};

export default ResponsiveCardRow;
