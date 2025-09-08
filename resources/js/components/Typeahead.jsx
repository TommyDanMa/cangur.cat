import React, { useEffect, useMemo, useRef, useState } from 'react';
import axios from 'axios';

export default function Typeahead({
  name,
  endpoint,
  field,
  initial = '',
  placeholder = 'Escriu per filtrar...',
  widthClass = 'w-64',
}) {
  const [open, setOpen] = useState(false);
  const [query, setQuery] = useState(initial);
  const [selected, setSelected] = useState(initial ? { id: String(initial), label: String(initial) } : null);
  const [options, setOptions] = useState([]);
  const [loading, setLoading] = useState(false);
  const [loaded, setLoaded] = useState(false); // evita dobles cargas iniciales
  const reqSeq = useRef(0);
  const lastQuery = useRef(null); // evita repetir la misma consulta
  const wrapRef = useRef(null);
  const inputRef = useRef(null);

  const fetchOptions = async (text) => {
    const current = ++reqSeq.current;
    setLoading(true);
    try {
      const { data } = await axios.get(endpoint, { params: { field, q: text ?? '' } });
      if (current !== reqSeq.current) return;
      setOptions(data);
    } catch (e) {
      console.error(e);
    } finally {
      if (current === reqSeq.current) setLoading(false);
    }
  };

  // Abrir y cargar al enfocar (primera vez)
  const handleFocus = () => {
    setOpen(true);
    if (!loaded) {
      lastQuery.current = '';       // forzamos primera consulta única
      fetchOptions('');
      setLoaded(true);
    }
  };

  // Cerrar al hacer click fuera
  useEffect(() => {
    const onDocClick = (e) => {
      if (!wrapRef.current) return;
      if (!wrapRef.current.contains(e.target)) setOpen(false);
    };
    document.addEventListener('mousedown', onDocClick);
    return () => document.removeEventListener('mousedown', onDocClick);
  }, []);

  // Debounce al escribir: solo consulta si cambia realmente la query
  useEffect(() => {
    if (!open) return;
    const id = setTimeout(() => {
      const q = query ?? '';
      if (q === lastQuery.current) return; // no repetir la misma
      lastQuery.current = q;
      fetchOptions(q);
    }, 250);
    return () => clearTimeout(id);
  }, [query, open]);

  const onSelect = (opt) => {
    setSelected(opt);
    setQuery(opt.label);
    setOpen(false);
  };

  const onKeyDown = (e) => {
    if (e.key === 'Escape') setOpen(false);
    if (e.key === 'ArrowDown') {
      if (!open) {
        setOpen(true);
        if (!loaded) {
          lastQuery.current = '';
          fetchOptions('');
          setLoaded(true);
        }
      }
    }
    if (e.key === 'Enter') {
      e.preventDefault();
      if (open && options.length > 0) onSelect(options[0]);
    }
  };

  const containerClass = useMemo(
    () => `inline-block ${widthClass} relative`, // relative para anclar el menú
    [widthClass]
  );

  return (
    <div className={containerClass} ref={wrapRef}>
      <div
        className="relative"
        onClick={() => {
          setOpen(true);
          if (inputRef.current) inputRef.current.focus();
          if (!loaded) {
            lastQuery.current = '';
            fetchOptions('');
            setLoaded(true);
          }
        }}
      >
        <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2 text-gray-500">
          <svg className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fillRule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.085l3.71-3.855a.75.75 0 111.08 1.04l-4.24 4.4a.75.75 0 01-1.08 0l-4.24-4.4a.75.75 0 01.02-1.06z" clipRule="evenodd" />
          </svg>
        </div>

        <input
          ref={inputRef}
          type="text"
          value={query}
          onChange={(e) => { setQuery(e.target.value); setOpen(true); }}
          onFocus={handleFocus}
          onKeyDown={onKeyDown}
          placeholder={placeholder}
          className="block w-full rounded-md border border-gray-300 bg-white pl-3 pr-8 py-2 text-sm
                     focus:outline-none focus:border-indigo-500 placeholder-gray-400"
          role="combobox"
          aria-autocomplete="list"
          aria-expanded={open}
          autoComplete="off"
        />
      </div>

      {open && (
        <ul className="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-56 overflow-auto">
          {loading && <li className="px-3 py-2 text-sm text-gray-500">Carregant...</li>}
          {!loading && options.length === 0 && (
            <li className="px-3 py-2 text-sm text-gray-500">Sense resultats</li>
          )}
          {!loading && options.map((opt) => (
            <li
              key={opt.id}
              onMouseDown={(e) => { e.preventDefault(); onSelect(opt); }}
              className="px-3 py-2 cursor-pointer text-sm hover:bg-gray-100"
              role="option"
            >
              {opt.label}
            </li>
          ))}
        </ul>
      )}

      <input type="hidden" name={name} value={selected ? selected.id : ''} />
    </div>
  );
}
