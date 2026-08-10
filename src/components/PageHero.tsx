import { Link } from "react-router-dom";
import { ChevronRight } from "lucide-react";

interface Crumb { label: string; to?: string; }

interface Props {
  eyebrow: string;
  title: string;
  description: string;
  image: string;
  crumbs?: Crumb[];
}

const PageHero = ({ eyebrow, title, description, image, crumbs }: Props) => (
  <section className="relative pt-16">
    <div className="relative h-[360px] md:h-[440px] overflow-hidden">
      {image && (
        <img
          src={image}
          alt={title}
          className="w-full h-full object-cover"
          loading="eager"
          decoding="async"
        />
      )}
      <div className="absolute inset-0 bg-gradient-to-r from-primary/95 via-primary/80 to-primary/40" />
      <div className="absolute inset-0 flex items-center">
        <div className="container mx-auto px-4 md:px-8 text-primary-foreground">
          {crumbs && (
            <div className="flex items-center gap-1.5 text-xs text-primary-foreground/70 mb-4 animate-fade-in">
              {crumbs.map((c, i) => (
                <span key={i} className="flex items-center gap-1.5">
                  {c.to ? (
                    // py-1.5 lifts this from a 16px-tall tap target to a comfortable one.
                    <Link to={c.to} className="inline-block py-1.5 hover:text-primary-foreground transition-colors">{c.label}</Link>
                  ) : (
                    <span className="text-primary-foreground">{c.label}</span>
                  )}
                  {i < crumbs.length - 1 && <ChevronRight className="w-3 h-3" />}
                </span>
              ))}
            </div>
          )}
          <span className="inline-block bg-accent text-accent-foreground text-xs font-bold uppercase tracking-wider px-3 py-1.5 rounded-full mb-4 animate-fade-in">
            {eyebrow}
          </span>
          <h1 className="text-4xl md:text-5xl lg:text-6xl font-extrabold font-heading leading-[1.1] mb-4 max-w-3xl animate-fade-in">
            {title}
          </h1>
          <p className="text-lg text-primary-foreground/80 max-w-2xl leading-relaxed animate-fade-in">
            {description}
          </p>
        </div>
      </div>
    </div>
  </section>
);

export default PageHero;
