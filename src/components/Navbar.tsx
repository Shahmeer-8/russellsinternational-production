import { useState, useEffect } from "react";
import { Menu, X } from "lucide-react";
import { Link, useLocation } from "react-router-dom";
import { useNavigation, useSettings } from "@/hooks/api";
import type { NavigationItem } from "@/types/api";
import { badgeClass, isExternalUrl } from "@/lib/navigation";
import russellsLogo from "@/assets/russells-logo.png";

const Navbar = () => {
  const [open, setOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);
  const location = useLocation();
  const { data: navigationData, isLoading: navigationLoading } = useNavigation();
  const { data: settingsData } = useSettings();
  const navLinks = navigationData?.data.header ?? [];
  const settings = settingsData?.data ?? {};
  const siteName = settings.site_name ?? "Russell's International";

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 20);
    window.addEventListener("scroll", onScroll);
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  useEffect(() => {
    window.scrollTo({ top: 0, behavior: "instant" as ScrollBehavior });
    setOpen(false);
  }, [location.pathname]);

  const isActive = (to: string) => !isExternalUrl(to) && location.pathname === to;

  const renderLink = (item: NavigationItem, mobile = false) => {
    const content = (
      <>
        <span>{item.label}</span>
        {item.badge_label && <span className={badgeClass(item)}>{item.badge_label}</span>}
      </>
    );
    const className = mobile
      ? `flex items-center gap-2 py-3 text-sm font-medium ${isActive(item.url) ? "text-accent" : "text-muted-foreground hover:text-foreground"}`
      : `inline-flex items-center gap-1.5 text-[13px] font-medium transition-colors ${isActive(item.url) ? "text-accent" : "text-muted-foreground hover:text-foreground"}`;

    if (isExternalUrl(item.url)) {
      return (
        <a key={item.id} href={item.url} target={item.target} rel={item.target === "_blank" ? "noreferrer" : undefined} className={className}>
          {content}
        </a>
      );
    }

    return (
      <Link key={item.id} to={item.url} className={className} onClick={() => mobile && setOpen(false)}>
        {content}
      </Link>
    );
  };

  return (
    <nav className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${
      scrolled
        ? "bg-background/95 backdrop-blur-lg border-b border-border shadow-sm"
        : "bg-background/80 backdrop-blur-sm"
    }`}>
      <div className="container mx-auto flex items-center justify-between h-16 px-4 md:px-8">
        <Link to="/" className="flex items-center">
          <img
            src={russellsLogo}
            alt={siteName}
            className="h-12 w-36 object-contain object-left md:w-44"
            width={483}
            height={163}
            fetchPriority="high"
          />
          <span className="sr-only">
            {siteName}
          </span>
        </Link>

        <div className="hidden lg:flex items-center gap-6">
          {navigationLoading ? (
            <div className="h-4 w-96 rounded bg-muted animate-pulse" />
          ) : (
            navLinks.map((item) => renderLink(item))
          )}
        </div>

        <Link to="/#contact" className="hidden lg:inline-flex btn-accent text-sm px-5 py-2.5">
          Start Your Journey
        </Link>

        <button className="lg:hidden p-2" onClick={() => setOpen(!open)} aria-label="Toggle menu">
          {open ? <X className="w-5 h-5 text-foreground" /> : <Menu className="w-5 h-5 text-foreground" />}
        </button>
      </div>

      {open && (
        <div className="lg:hidden bg-background border-t border-border px-4 pb-4 animate-fade-in max-h-[70vh] overflow-y-auto">
          {navLinks.map((item) => renderLink(item, true))}
          <Link to="/#contact" className="block mt-2 btn-accent text-sm text-center" onClick={() => setOpen(false)}>
            Start Your Journey
          </Link>
        </div>
      )}
    </nav>
  );
};

export default Navbar;
