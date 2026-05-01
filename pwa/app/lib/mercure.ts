import { useEffect, useRef, useState } from "react";
import axios from "axios";
import { ENTRYPOINT } from "../../config/entrypoint";

export type AiReservationStats = {
  pending: number;
  analyzing: number;
  proposing: number;
  awaiting_customer: number;
  awaiting_club: number;
  confirmed: number;
  cancelled: number;
  total: number;
};

const EMPTY_STATS: AiReservationStats = {
  pending: 0,
  analyzing: 0,
  proposing: 0,
  awaiting_customer: 0,
  awaiting_club: 0,
  confirmed: 0,
  cancelled: 0,
  total: 0,
};

/**
 * Returns the public URL of the Mercure hub for the current origin.
 * Caddy serves the hub at /.well-known/mercure on the same host as the API.
 */
const getHubUrl = (): string | null => {
  if (typeof window === "undefined") return null;
  return `${window.location.origin}/.well-known/mercure`;
};

let authPromise: Promise<void> | null = null;

/**
 * Requests a Mercure subscriber JWT cookie for the current authenticated user.
 * The cookie scopes the user to the topics of the clients they have access to.
 * Memoized per session to avoid spamming the auth endpoint.
 */
const ensureMercureCookie = (accessToken?: string): Promise<void> => {
  if (authPromise) return authPromise;
  authPromise = axios
    .post(
      `${ENTRYPOINT}/admin/mercure/auth`,
      {},
      {
        headers: accessToken ? { Authorization: `Bearer ${accessToken}` } : undefined,
        withCredentials: true,
      }
    )
    .then(() => undefined)
    .catch((err) => {
      authPromise = null;
      throw err;
    });
  return authPromise;
};

/**
 * Subscribe to live AI reservation stats for a given client over Mercure.
 *
 * - Performs an initial REST fetch to seed the state (covers the gap between
 *   page mount and first Mercure event).
 * - Opens an EventSource on the per-client private topic.
 * - Updates the state on every event.
 * - Cleans up the EventSource on unmount or clientId change.
 */
export const useAiReservationStats = (
  clientId: number | string | null | undefined,
  accessToken: string | null | undefined,
  enabled: boolean = true,
): AiReservationStats => {
  const [stats, setStats] = useState<AiReservationStats>(EMPTY_STATS);
  const eventSourceRef = useRef<EventSource | null>(null);
  const accessTokenRef = useRef(accessToken);
  accessTokenRef.current = accessToken;

  useEffect(() => {
    if (!enabled || !clientId || !accessToken) {
      setStats(EMPTY_STATS);
      return;
    }

    let cancelled = false;

    const cleanup = () => {
      if (eventSourceRef.current) {
        eventSourceRef.current.close();
        eventSourceRef.current = null;
      }
    };

    const setup = async () => {
      try {
        const initial = await axios.get<AiReservationStats>(
          `${ENTRYPOINT}/admin/ai-reservation/stats?clientId=${clientId}`,
          { headers: { Authorization: `Bearer ${accessTokenRef.current}` } }
        );
        if (cancelled) return;
        setStats(initial.data);
      } catch {
        // Initial fetch failed; leave state as-is.
      }

      try {
        await ensureMercureCookie(accessTokenRef.current ?? undefined);
      } catch {
        return;
      }

      const hubUrl = getHubUrl();
      if (!hubUrl || cancelled) return;

      const url = new URL(hubUrl);
      url.searchParams.append("topic", `/admin/ai-reservation/stats/${clientId}`);

      cleanup();
      const es = new EventSource(url.toString(), { withCredentials: true });
      eventSourceRef.current = es;
      es.onmessage = (event) => {
        try {
          const payload = JSON.parse(event.data) as AiReservationStats;
          setStats(payload);
        } catch {
          // Ignore malformed payloads.
        }
      };
      es.onerror = () => {
        // Browser will auto-reconnect; nothing to do here.
      };
    };

    setup();

    return () => {
      cancelled = true;
      cleanup();
    };
  }, [clientId, enabled]);

  return stats;
};
