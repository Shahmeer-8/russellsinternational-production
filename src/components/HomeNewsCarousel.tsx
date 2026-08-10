import { Link } from "react-router-dom";
import { ArrowRight, Calendar } from "lucide-react";
import {
  Carousel,
  CarouselContent,
  CarouselItem,
  CarouselNext,
  CarouselPrevious,
} from "@/components/ui/carousel";
import { useEvents } from "@/hooks/api";
import { useSectionCopy } from "@/hooks/useSectionCopy";

type Tag = "Event" | "News" | "Workshop";

const tagColor: Record<Tag, string> = {
  Event: "bg-accent text-accent-foreground",
  News: "bg-primary text-primary-foreground",
  Workshop: "bg-cta text-cta-foreground",
};

const HomeNewsCarousel = () => {
  const copy = useSectionCopy("home", "news");
  const { data, isLoading } = useEvents();
  const displayItems = (data?.data?.data ?? []).slice(0, 8).map((event) => ({
    image: event.image_url,
    title: event.title,
    desc: event.short_description,
    date: event.formatted_date ?? event.event_date ?? "",
    tag: (event.content_type === "news" ? "News" : event.tag === "Workshop" ? "Workshop" : "Event") as Tag,
  }));

  return (
  <section className="py-20 md:py-28 bg-background">
    <div className="container mx-auto px-4 md:px-8">
      <div className="text-center mb-12">
        <span className="section-label">{copy("eyebrow", "Stay Updated")}</span>
        <h2 className="section-title mt-3">{copy("title", "Latest News & Events")}</h2>
        <p className="text-muted-foreground mt-4 max-w-xl mx-auto">
          {copy("subtitle", "Stay updated with our latest activities, events, and announcements.")}
        </p>
      </div>

      {isLoading ? (
        <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
          {[...Array(4)].map((_, i) => <div key={i} className="h-72 rounded-2xl bg-muted animate-pulse" />)}
        </div>
      ) : displayItems.length === 0 ? null : (
        <Carousel opts={{ align: "start", loop: true }} className="relative">
          <CarouselContent className="-ml-4">
            {displayItems.map((it) => (
            <CarouselItem
              key={it.title}
              className="pl-4 basis-full sm:basis-1/2 lg:basis-1/3 xl:basis-1/4"
            >
              <Link
                to="/events"
                className="group block h-full bg-card rounded-2xl overflow-hidden ring-1 ring-border shadow-[var(--card-shadow)] hover:shadow-[var(--card-shadow-hover)] hover:-translate-y-1.5 transition-all duration-500"
              >
                <div className="relative h-48 overflow-hidden">
                  {it.image && (
                    <img
                      src={it.image}
                      alt={it.title}
                      loading="lazy"
                      decoding="async"
                      className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                    />
                  )}
                  <span
                    className={`absolute top-3 left-3 text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full ${tagColor[it.tag]}`}
                  >
                    {it.tag}
                  </span>
                </div>
                <div className="p-5">
                  <div className="flex items-center gap-1.5 text-xs text-muted-foreground mb-2">
                    <Calendar className="w-3.5 h-3.5" />
                    <span>{it.date}</span>
                  </div>
                  <h3 className="font-heading font-bold text-foreground leading-snug mb-1.5 group-hover:text-accent transition-colors line-clamp-2">
                    {it.title}
                  </h3>
                  <p className="text-sm text-muted-foreground line-clamp-1">{it.desc}</p>
                </div>
              </Link>
            </CarouselItem>
            ))}
          </CarouselContent>
          <CarouselPrevious className="hidden md:flex -left-4" />
          <CarouselNext className="hidden md:flex -right-4" />
        </Carousel>
      )}

      {displayItems.length > 0 && (
        <div className="text-center mt-12">
        <Link
          to="/events"
          className="btn-accent inline-flex items-center gap-2"
        >
          Explore More <ArrowRight className="w-4 h-4" />
        </Link>
        </div>
      )}
    </div>
  </section>
  );
};

export default HomeNewsCarousel;
