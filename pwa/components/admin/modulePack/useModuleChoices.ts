import { useState, useEffect } from "react";
import { useSessionContext } from "../SessionContextProvider";

const ENTRYPOINT = process.env.NEXT_PUBLIC_ENTRYPOINT || "";

export const useModuleChoices = () => {
  const [choices, setChoices] = useState<{ id: string; name: string }[]>([]);
  const [loading, setLoading] = useState(true);
  const { session } = useSessionContext();

  useEffect(() => {
    const fetchModules = async () => {
      try {
        const res = await fetch(`${ENTRYPOINT}/admin/integrations/modules`, {
          headers: { Authorization: `Bearer ${session?.accessToken}` },
        });
        if (res.ok) setChoices(await res.json());
      } catch { /* fallback silencieux */ }
      setLoading(false);
    };
    if (session?.accessToken) fetchModules();
  }, [session?.accessToken]);

  return { choices, loading };
};
