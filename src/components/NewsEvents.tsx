import { useState } from "react";
import { Calendar, ArrowRight } from "lucide-react";
import { useScrollReveal } from "@/hooks/useScrollReveal";
import DetailDrawer from "@/components/DetailDrawer";
import { useEvents } from "@/hooks/api";
import { useSectionCopy } from "@/hooks/useSectionCopy";

type EventCard = {
  image: string | null;
  tag: string;
  tagColor: string;
  title: string;
  date: string;
  desc: string;
  details: string;
};

const NewsEvents = () => {
  const copy = useSectionCopy("events", "news");
  const { ref, visible } = useScrollReveal();
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [selected, setSelected] = useState<EventCard | null>(null);

  const { data: eventsData, isLoading } = useEvents("event");
  const eventsList = (eventsData?.data?.data ?? []).map((e) => ({
    image: e.image_url,
    tag: e.tag,
    tagColor: e.tag_color,
    title: e.title,
    date: e.formatted_date ?? e.event_date ?? "",
    desc: e.short_description,
    details: e.full_details ?? "",
  }));

  const openDrawer = (e: EventCard) => {
    setSelected(e);
    setDrawerOpen(true);
  };

  return (
    <>
      <section className="py-20 md:py-28 bg-section-alt">
        <div
          ref={ref}
          className={`container mx-auto px-4 md:px-8 transition-all duration-700 ${visible ? "opacity-100" : "opacity-0"}`}
        >
          <div className="flex flex-col sm:flex-row sm:items-end sm:justify-between mb-14 gap-4">
            <div>
              <span className="section-label">{copy("eyebrow", "News & Events")}</span>
              <h2 className="section-title mt-3">{copy("title", "What's Happening")}</h2>
              <p className="text-muted-foreground mt-3 max-w-md">{copy("subtitle", "Stay updated with our latest events, workshops, and admissions announcements.")}</p>
            </div>
            <a href="#" className="inline-flex items-center gap-2 py-3 text-sm font-semibold text-accent hover:gap-3 transition-all shrink-0">
              View All News <ArrowRight className="w-4 h-4" />
            </a>
          </div>

          {isLoading ? (
            <div className="grid md:grid-cols-3 gap-6">
              {[...Array(3)].map((_, i) => <div key={i} className="premium-card h-80 animate-pulse" />)}
            </div>
          ) : eventsList.length === 0 ? null : (
            <div className="grid md:grid-cols-3 gap-6">
              {eventsList.map((e) => (
                <div key={e.title} className="premium-card overflow-hidden group cursor-pointer" onClick={() => openDrawer(e)}>
                  <div className="h-48 overflow-hidden bg-muted">
                    {e.image && (
                      <img src={e.image} alt={e.title} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" decoding="async" width={800} height={512} />
                    )}
                  </div>
                  <div className="p-6">
                    <div className="flex items-center gap-3 mb-3">
                      <span className={`text-[11px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full ${e.tagColor}`}>{e.tag}</span>
                      <span className="flex items-center gap-1.5 text-xs text-muted-foreground"><Calendar className="w-3 h-3" /> {e.date}</span>
                    </div>
                    <h3 className="font-bold text-foreground font-heading text-base mb-2 group-hover:text-accent transition-colors leading-snug">{e.title}</h3>
                    <p className="text-sm text-muted-foreground leading-relaxed">{e.desc}</p>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </section>

      <DetailDrawer open={drawerOpen} onClose={() => setDrawerOpen(false)} title={selected?.title || "Event Details"}>
        {selected && (
          <div className="space-y-6">
            {selected.image && <img src={selected.image} alt={selected.title} className="w-full h-48 object-cover rounded-xl" decoding="async" />}
            <div className="flex items-center gap-3">
              <span className={`text-[11px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full ${selected.tagColor}`}>{selected.tag}</span>
              <span className="flex items-center gap-1.5 text-sm text-muted-foreground"><Calendar className="w-4 h-4" /> {selected.date}</span>
            </div>
            <div>
              <h4 className="font-heading font-bold text-xl text-foreground mb-2">{selected.title}</h4>
              <p className="text-muted-foreground leading-relaxed">{selected.desc}</p>
            </div>
            {selected.details && (
              <div>
                <h5 className="font-semibold text-foreground mb-2">More Details</h5>
                <p className="text-sm text-muted-foreground leading-relaxed">{selected.details}</p>
              </div>
            )}
          </div>
        )}
      </DetailDrawer>
    </>
  );
};

export default NewsEvents;
