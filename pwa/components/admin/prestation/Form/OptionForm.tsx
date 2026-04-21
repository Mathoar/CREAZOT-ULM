"use client";

import React, { useEffect, useState } from "react";
import { useDataProvider } from "react-admin";
import Select from 'react-select';

export const OptionForm = ({
  selectedOptions,
  setSelectedOptions,
  isUpdate = false,
  reservation = null,
  quantite = 1,
  splitOptions = false,
  setSplitOptions = null,
}) => {
  const dataProvider = useDataProvider();
  const [options, setOptions] = useState([]);

  useEffect(() => {
    dataProvider
      .getList('options', {})
      .then(({ data }) => {
        const available = data.filter(o => o.isAvailable !== false);
        const formatted = available.map(o => ({
          ...o,
          value: o['@id'],
          label: `${o.nom}${o.prix > 0 ? ` (+${o.prix.toFixed(2)} €)` : ''}`,
        }));
        setOptions(formatted);

        if (isUpdate && reservation?.selectedOptions) {
          const allFormatted = data.map(o => ({
            ...o,
            value: o['@id'],
            label: `${o.nom}${o.prix > 0 ? ` (+${o.prix.toFixed(2)} €)` : ''}`,
          }));
          const preselected = allFormatted.filter(o =>
            reservation.selectedOptions.some(s => (s['@id'] || s) === o['@id'])
          );
          setSelectedOptions(preselected);
        }
      });
  }, []);

  const showSplitCheckbox = !isUpdate && setSplitOptions && quantite > 1 && selectedOptions.length > 1;

  return (
    <div className="mb-2">
      <label className="mb-2 block text-sm font-medium text-black dark:text-white">
        Options
      </label>
      <Select
        closeMenuOnSelect={false}
        className="basic-multi-select"
        value={selectedOptions}
        isMulti
        options={options}
        onChange={selected => setSelectedOptions(selected || [])}
        placeholder="Sélectionner des options..."
        noOptionsMessage={() => "Aucune option disponible"}
        styles={{
          control: (baseStyles) => ({
            ...baseStyles,
            minHeight: '41px',
          }),
          menu: (baseStyles) => ({
            ...baseStyles,
            zIndex: 50,
          }),
        }}
      />
      { showSplitCheckbox &&
        <label className="mt-1 flex items-center gap-1.5 cursor-pointer text-xs text-gray-500 dark:text-gray-400">
          <input
            type="checkbox"
            checked={splitOptions}
            onChange={e => setSplitOptions(e.target.checked)}
            className="h-3.5 w-3.5 rounded border-gray-300"
          />
          Répartir les options sur chaque vol
        </label>
      }
    </div>
  );
};
