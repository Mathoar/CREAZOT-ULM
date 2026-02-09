import { useFormContext, useWatch } from 'react-hook-form';
import { useEffect, useRef } from 'react';

export const AutoSyncDetails = () => {
  const { control, setValue } = useFormContext();

  const prix = useWatch({ control, name: 'prix' });
  const details = useWatch({ control, name: 'details' });

  useEffect(() => {
    if (!details?.length) return;

    const first = details[0];
    if (!first?.isAuto) return;

    const autres = details
      .slice(1)
      .reduce((sum, d) => sum + (Number(d?.amount) || 0), 0);

    const reste = Math.max((prix ?? 0) - autres, 0);

    setValue('details.0.amount', reste, { shouldDirty: false });
  }, [prix, details?.length]);

  return null;
};