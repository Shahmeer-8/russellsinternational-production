import { usePageSections } from "@/hooks/api";
import { sectionText } from "@/lib/content";

type CopyField = Parameters<typeof sectionText>[1];

/**
 * Reads an admin-editable heading for one section of a page.
 *
 * Returns a getter that falls back to the supplied text, so a missing or hidden
 * page_sections row leaves the component rendering exactly what it did when the
 * words were hardcoded. Several components on the same page share one request:
 * usePageSections is keyed per page, so TanStack Query dedupes them.
 *
 *   const copy = useSectionCopy("home", "why_choose_us");
 *   copy("title", "Your Trusted Partner in Growth")
 */
export function useSectionCopy(page: string, sectionKey: string) {
  const { data } = usePageSections(page);
  const section = data?.data?.[sectionKey];

  return (field: CopyField, fallback: string) => sectionText(section, field, fallback);
}
