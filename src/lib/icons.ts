import {
  Award,
  BookOpenText,
  Brain,
  Briefcase,
  Code,
  GraduationCap,
  Globe,
  Headphones,
  Languages,
  MessageCircle,
  Palette,
  Plane,
  ScrollText,
  Server,
  Shield,
  ShieldCheck,
  Sparkles,
  TrendingUp,
  Users,
} from "lucide-react";
import type { ElementType } from "react";

/**
 * Every icon the admin can choose, keyed by the Lucide name stored in
 * `icon_name`. Keep this in sync with AdminChoices::icons() on the backend —
 * LanguageSectionAdminTest asserts that every admin option exists here.
 */
export const ICON_MAP: Record<string, ElementType> = {
  Award,
  BookOpenText,
  Brain,
  Briefcase,
  Code,
  GraduationCap,
  Globe,
  Headphones,
  Languages,
  MessageCircle,
  Palette,
  Plane,
  ScrollText,
  Server,
  Shield,
  ShieldCheck,
  Sparkles,
  TrendingUp,
  Users,
};

export const DEFAULT_ICON: ElementType = Globe;

/**
 * Resolve a stored icon name to a component. Unknown or missing names fall back
 * rather than throwing, so bad data can never blank out a page.
 */
export function resolveIcon(name?: string | null, fallback: ElementType = DEFAULT_ICON): ElementType {
  if (!name) {
    return fallback;
  }

  return ICON_MAP[name] ?? fallback;
}
