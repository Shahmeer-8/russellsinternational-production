import { useState } from "react";
import { ArrowRight, Sparkles } from "lucide-react";
import { useScrollReveal } from "@/hooks/useScrollReveal";
import DetailDrawer from "@/components/DetailDrawer";
import ResponsiveCardRow from "@/components/ResponsiveCardRow";
import { useServices } from "@/hooks/api";
import { useSectionCopy } from "@/hooks/useSectionCopy";
import { resolveIcon } from "@/lib/icons";
import type { Service } from "@/types/api";

/**
 * The services were fully editable in the admin but rendered nowhere: six curated
 * entries — IT training, study abroad, IELTS, NAVTTC, career counselling, corporate
 * training — that no visitor could see.
 *
 * Uses the shared mobile carousel so six cards cost one card of height on a phone.
 */
const ServicesSection = () => {
  const { ref, visible } = useScrollReveal();
  const copy = useSectionCopy("home", "services");
  const { data, isLoading } = useServices();
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [selected, setSelected] = useState<Service | null>(null);

  const services = data?.data ?? [];

  if (isLoading || services.length === 0) {
    return null;
  }

  const cards = services.map((service) => {
    const Icon = resolveIcon(service.icon_name, Sparkles);
    const [bg, fg] = (service.color_class || "bg-blue-50 text-blue-600").split(" ");

    return {
      key: service.id,
      node: (
        <div
          className="premium-card p-6 group cursor-pointer h-full"
          onClick={() => {
            setSelected(service);
            setDrawerOpen(true);
          }}
        >
          <div className={`w-12 h-12 rounded-xl ${bg} flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300`}>
            <Icon className={`w-6 h-6 ${fg}`} />
          </div>
          <h3 className="font-bold text-foreground font-heading text-lg mb-2 group-hover:text-accent transition-colors">
            {service.title}
          </h3>
          <p className="text-sm text-muted-foreground leading-relaxed mb-5">{service.description}</p>
          <span className="inline-flex items-center gap-1.5 text-sm font-semibold text-accent group-hover:gap-2.5 transition-all">
            Learn More <ArrowRight className="w-3.5 h-3.5" />
          </span>
        </div>
      ),
    };
  });

  return (
    <>
      <section className="py-20 md:py-28">
        <div
          ref={ref}
          className={`container mx-auto px-4 md:px-8 transition-all duration-700 ${visible ? "opacity-100" : "opacity-0"}`}
        >
          <div className="text-center mb-10">
            <span className="section-label">{copy("eyebrow", "What We Do")}</span>
            <h2 className="section-title mt-3">{copy("title", "Everything You Need in One Place")}</h2>
            <p className="text-muted-foreground mt-4 max-w-2xl mx-auto">
              {copy("subtitle", "From skills training to study abroad and career placement, all under one roof.")}
            </p>
          </div>

          <ResponsiveCardRow items={cards} />
        </div>
      </section>

      <DetailDrawer open={drawerOpen} onClose={() => setDrawerOpen(false)} title={selected?.title || "Service"}>
        {selected && (() => {
          const Icon = resolveIcon(selected.icon_name, Sparkles);
          const [bg, fg] = (selected.color_class || "bg-blue-50 text-blue-600").split(" ");

          return (
            <div className="space-y-6">
              <div className={`w-16 h-16 rounded-2xl ${bg} flex items-center justify-center`}>
                <Icon className={`w-8 h-8 ${fg}`} />
              </div>
              <div>
                <h4 className="font-heading font-bold text-xl text-foreground mb-2">{selected.title}</h4>
                <p className="text-muted-foreground leading-relaxed">{selected.description}</p>
              </div>
              {selected.details && (
                <p className="text-sm text-muted-foreground leading-relaxed">{selected.details}</p>
              )}
              {selected.key_benefits && selected.key_benefits.length > 0 && (
                <div>
                  <h5 className="font-semibold text-foreground mb-3">What's Included</h5>
                  <ul className="space-y-2">
                    {selected.key_benefits.map((benefit) => (
                      <li key={benefit} className="flex items-start gap-2 text-sm text-muted-foreground">
                        <div className="w-1.5 h-1.5 rounded-full bg-accent mt-1.5 shrink-0" />
                        {benefit}
                      </li>
                    ))}
                  </ul>
                </div>
              )}
            </div>
          );
        })()}
      </DetailDrawer>
    </>
  );
};

export default ServicesSection;
