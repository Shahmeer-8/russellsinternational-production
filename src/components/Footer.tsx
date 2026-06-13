import { Mail, Phone, MapPin, Facebook, Instagram, Linkedin, Youtube } from "lucide-react";
import { Link } from "react-router-dom";
import { useNavigation, useSettings } from "@/hooks/api";
import { badgeClass, isExternalUrl } from "@/lib/navigation";
import type { NavigationItem } from "@/types/api";
import russellsLogo from "@/assets/russells-logo.png";

const socials = [
  { icon: Facebook, keys: ["facebook_url", "facebook"], label: "Facebook" },
  { icon: Instagram, keys: ["instagram_url", "instagram"], label: "Instagram" },
  { icon: Linkedin, keys: ["linkedin_url", "linkedin"], label: "LinkedIn" },
  { icon: Youtube, keys: ["youtube_url", "youtube"], label: "YouTube" },
];

const Footer = () => {
  const { data, isLoading } = useSettings();
  const { data: navigationData, isLoading: navigationLoading } = useNavigation();
  const settings = data?.data ?? {};
  const columns = navigationData?.data.footer ?? [];
  const availableSocials = socials
    .map((s) => ({ ...s, href: s.keys.map((key) => settings[key]).find(Boolean) }))
    .filter((s) => s.href);
  const siteName = settings.site_name;
  const footerText = settings.footer_text ?? settings.footer_about;
  const mapUrl = settings.map_iframe_url ?? settings.google_map;
  const renderFooterLink = (item: NavigationItem) => {
    const content = (
      <>
        <span>{item.label}</span>
        {item.badge_label && <span className={badgeClass(item)}>{item.badge_label}</span>}
      </>
    );
    const className = "inline-flex items-center gap-1.5 text-sm text-primary-foreground/50 hover:text-accent transition-colors";

    if (isExternalUrl(item.url)) {
      return <a href={item.url} target={item.target} rel={item.target === "_blank" ? "noreferrer" : undefined} className={className}>{content}</a>;
    }

    return <Link to={item.url} className={className}>{content}</Link>;
  };

  if (isLoading) {
    return (
      <footer className="bg-primary text-primary-foreground" aria-hidden="true">
        <div className="container mx-auto px-4 md:px-8 py-12 md:py-16">
          <div className="h-64 rounded-2xl bg-primary-foreground/10 animate-pulse" />
        </div>
      </footer>
    );
  }

  return (
    <footer className="bg-primary text-primary-foreground">
      <div className="container mx-auto px-4 md:px-8 py-12 md:py-16">
        <div className="grid lg:grid-cols-12 gap-10">
          <div className="lg:col-span-4">
            <div className="mb-4">
              <img
                src={russellsLogo}
                alt={siteName ?? "Russell's International"}
                className="h-20 w-56 object-contain object-left"
                width={500}
                height={500}
                loading="lazy"
              />
            </div>
            {footerText && (
              <p className="text-sm text-primary-foreground/50 leading-relaxed mb-5">
                {footerText}
              </p>
            )}
            <ul className="space-y-2.5 text-sm">
              {settings.phone && (
                <li className="flex items-center gap-2.5 text-primary-foreground/70">
                  <Phone className="w-4 h-4 text-accent" /> {settings.phone}
                </li>
              )}
              {settings.email && (
                <li className="flex items-center gap-2.5 text-primary-foreground/70">
                  <Mail className="w-4 h-4 text-accent" /> {settings.email}
                </li>
              )}
              {settings.address && (
                <li className="flex items-start gap-2.5 text-primary-foreground/70">
                  <MapPin className="w-4 h-4 text-accent mt-0.5 shrink-0" /> {settings.address}
                </li>
              )}
            </ul>
            {availableSocials.length > 0 && (
              <div className="flex gap-2 mt-5">
                {availableSocials.map((s) => (
                  <a
                    key={s.label}
                    href={s.href}
                    aria-label={s.label}
                    className="w-9 h-9 rounded-full bg-primary-foreground/10 hover:bg-accent flex items-center justify-center transition-colors"
                  >
                    <s.icon className="w-4 h-4" />
                  </a>
                ))}
              </div>
            )}
          </div>

          <div className="lg:col-span-4 grid grid-cols-2 sm:grid-cols-3 gap-8">
            {navigationLoading ? (
              <div className="col-span-full h-32 rounded bg-primary-foreground/10 animate-pulse" />
            ) : columns.map((col) => (
              <div key={col.title}>
                <h4 className="font-heading font-bold text-sm mb-4">{col.title}</h4>
                <ul className="space-y-2.5">
                  {col.links.map((item) => (
                    <li key={item.id}>
                      {renderFooterLink(item)}
                    </li>
                  ))}
                </ul>
              </div>
            ))}
          </div>

          <div className="lg:col-span-4">
            <h4 className="font-heading font-bold text-sm mb-4">Find Us</h4>
            {mapUrl && (
              <div className="rounded-2xl overflow-hidden ring-1 ring-primary-foreground/10 h-56">
                <iframe
                  title="Russell's International location"
                  src={mapUrl}
                  loading="lazy"
                  referrerPolicy="no-referrer-when-downgrade"
                  className="w-full h-full border-0"
                />
              </div>
            )}
          </div>
        </div>

        <div className="border-t border-primary-foreground/10 mt-10 pt-6 flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-primary-foreground/40">
          {settings.copyright_text && <span>{settings.copyright_text}</span>}
          <div className="flex gap-6">
            {settings.privacy_url && <a href={settings.privacy_url} className="hover:text-primary-foreground transition-colors">Privacy Policy</a>}
            {settings.terms_url && <a href={settings.terms_url} className="hover:text-primary-foreground transition-colors">Terms of Service</a>}
          </div>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
