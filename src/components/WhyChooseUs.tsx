import { Award } from "lucide-react";
import { useScrollReveal } from "@/hooks/useScrollReveal";
import { useWhyChooseUs } from "@/hooks/api";
import { resolveIcon } from "@/lib/icons";

const WhyChooseUs = () => {
  const { ref, visible } = useScrollReveal();
  const { data, isLoading } = useWhyChooseUs();
  const points = (data?.data ?? []).map((item) => ({
    icon: resolveIcon(item.icon_name, Award),
    title: item.title,
    desc: item.description,
    color: item.color_class,
  }));

  return (
    <section id="why-us" className="py-20 md:py-28 bg-section-alt">
      <div
        ref={ref}
        className={`container mx-auto px-4 md:px-8 transition-all duration-700 ${visible ? "opacity-100" : "opacity-0"}`}
      >
        <div className="text-center mb-14">
          <span className="section-label">Why Russell's International</span>
          <h2 className="section-title mt-3">Your Trusted Partner in Growth</h2>
        </div>

        {isLoading ? (
          <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {[...Array(6)].map((_, i) => <div key={i} className="premium-card h-48 animate-pulse" />)}
          </div>
        ) : points.length === 0 ? null : (
          <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {points.map((p, i) => (
              <div
                key={p.title}
                className="premium-card p-7 group"
                style={{ transitionDelay: `${i * 80}ms` }}
              >
                <div className={`w-12 h-12 rounded-xl ${p.color.split(" ")[0] ?? "bg-primary/10"} flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300`}>
                  <p.icon className={`w-6 h-6 ${p.color.split(" ")[1] ?? "text-primary"}`} />
                </div>
                <h3 className="font-bold text-foreground font-heading text-lg mb-2">{p.title}</h3>
                <p className="text-sm text-muted-foreground leading-relaxed">{p.desc}</p>
              </div>
            ))}
          </div>
        )}
      </div>
    </section>
  );
};

export default WhyChooseUs;
