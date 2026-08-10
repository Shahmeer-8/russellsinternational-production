// ─── Shared ───────────────────────────────────────────────────────────────────

export interface ApiResponse<T> {
  success: boolean;
  data: T;
  message?: string;
}

export interface PaginatedResponse<T> {
  success: boolean;
  data: {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

// ─── Home ──────────────────────────────────────────────────────────────────────

export interface HeroSlide {
  id: number;
  eyebrow: string;
  title: string;
  description: string;
  cta_label: string;
  cta_url: string;
  secondary_cta_label: string;
  secondary_cta_url: string;
  image_url: string | null;
  sort_order: number;
  is_active: boolean;
}

export interface TickerItem {
  id: number;
  emoji: string | null;
  text: string;
  sort_order: number;
}

export interface Stat {
  id: number;
  value: string;
  label: string;
  icon_name: string | null;
}

// ─── Services / Why Choose Us ──────────────────────────────────────────────────

export interface Service {
  id: number;
  icon_name: string;
  title: string;
  description: string;
  details: string;
  color_class: string;
  key_benefits: string[] | null;
}

export interface WhyChooseUsItem {
  id: number;
  icon_name: string;
  title: string;
  description: string;
  color_class: string;
}

// ─── Courses ───────────────────────────────────────────────────────────────────

export interface Course {
  id: number;
  type: 'paid' | 'navttc';
  icon_name: string;
  title: string;
  description: string | null;
  image_url: string | null;
  duration: string;
  students_count: string;
  price: string | null;
  tag: string | null;
  color_class: string;
  what_you_learn: string[] | null;
  highlights: string[] | null;
  pdf_url: string | null;
}

// ─── Study Abroad ──────────────────────────────────────────────────────────────

export interface StudyDestination {
  id: number;
  flag_emoji: string;
  country: string;
  partner_unis_count: string;
  description: string;
  highlight_unis: string;
  intake_periods: string;
  visa_success_rate: string;
  services: string[] | null;
  scholarships: string[] | null;
  image_url: string | null;
}

// ─── Languages ─────────────────────────────────────────────────────────────────

export interface LanguageProgram {
  id: number;
  title: string;
  duration: string;
  badge: string;
  description: string;
  benefits: string[];
  color_class: string;
  icon_name: string | null;
  image_url: string | null;
}

/** A tab on the Languages page, with the programs filed under it. */
export interface LanguageSection {
  id: number;
  slug: string;
  label: string;
  short_label: string | null;
  tab_label: string;
  heading: string;
  subtitle: string | null;
  icon_name: string;
  color_class: string;
  sort_order: number;
  programs: LanguageProgram[];
}

// ─── Careers ───────────────────────────────────────────────────────────────────

export interface Job {
  id: number;
  title: string;
  company: string;
  location: string;
  type: 'Full-Time' | 'Part-Time' | 'Contract' | 'Remote';
  salary: string | null;
  description: string;
  requirements: string[] | null;
  deadline: string | null;
}

export interface Internship {
  id: number;
  title: string;
  company: string;
  location: string;
  duration: string;
  type: string;
  description: string;
  skills: string[] | null;
  gains: string[] | null;
  image_url: string | null;
}

// ─── Events / Gallery ──────────────────────────────────────────────────────────

export interface Event {
  id: number;
  content_type: 'event' | 'news';
  tag: string;
  tag_color: string;
  title: string;
  event_date: string | null;
  formatted_date: string | null;
  short_description: string;
  full_details: string | null;
  image_url: string | null;
  venue: string | null;
  is_featured: boolean;
}

export interface GalleryPhoto {
  id: number;
  image_url: string;
  alt_text: string;
  category: string;
  sort_order: number;
}

// ─── Testimonials ──────────────────────────────────────────────────────────────

export interface Testimonial {
  id: number;
  type: 'written' | 'video';
  name: string;
  program: string;
  quote: string | null;
  image_url: string | null;
  youtube_id: string | null;
  rating: number;
}

// ─── Settings ──────────────────────────────────────────────────────────────────

export interface Settings {
  [key: string]: string;
}

export interface NavigationItem {
  id: number;
  location: 'header' | 'footer';
  footer_column: string | null;
  label: string;
  url: string;
  target: '_self' | '_blank';
  badge_label: string | null;
  badge_variant: string | null;
  badge_animation: string | null;
  sort_order: number;
  is_active: boolean;
}

export interface FooterNavigationColumn {
  title: string;
  links: NavigationItem[];
}

export interface NavigationPayload {
  header: NavigationItem[];
  footer: FooterNavigationColumn[];
}

// ─── Page SEO ──────────────────────────────────────────────────────────────────

export interface PageMeta {
  slug: string;
  meta_title: string | null;
  meta_description: string | null;
  og_image_url: string | null;
}

export interface PageSection {
  id: number;
  page_slug: string;
  section_key: string;
  name: string;
  eyebrow: string | null;
  title: string | null;
  subtitle: string | null;
  body: string | null;
  image_url: string | null;
  cta_label: string | null;
  cta_url: string | null;
  secondary_cta_label: string | null;
  secondary_cta_url: string | null;
  items: Record<string, string> | null;
  extra: Record<string, string> | null;
  sort_order: number;
  is_active: boolean;
}

export type PageSections = Record<string, PageSection>;

export interface TeamMember {
  id: number;
  name: string;
  role: string;
  bio: string;
  image_url: string | null;
  linkedin_url: string | null;
}

// ─── Form payloads ─────────────────────────────────────────────────────────────

export interface ContactPayload {
  name: string;
  phone?: string;
  email: string;
  interest?: string;
  message?: string;
}

export interface CareerApplicationPayload {
  application_type: 'job' | 'internship';
  position_id?: number;
  position_title: string;
  name: string;
  email: string;
  phone?: string;
  cover_letter?: string;
  portfolio_url?: string;
  cv?: File;
}
