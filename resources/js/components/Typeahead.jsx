// Typeahead.jsx
// Purpose: A small autocomplete (typeahead) input that fetches options from the server as you type.
// Audience: Beginner-friendly comments explaining React hooks, event handlers, and data flow.
// Python/Flask parallels are included to help backend-focused developers.

import React, { useEffect, useMemo, useRef, useState } from 'react';
import axios from 'axios';

/**
 * Props (think of these like function parameters in Python):
 * - name: string — the name of the hidden input sent with the HTML form submission.
 * - endpoint: string — the URL to GET options from (similar to a Flask route like /api/search).
 * - field: string — sent as a query param to the endpoint so the server knows what to search by.
 * - initial: string — optional initial value to prefill the input.
 * - placeholder: string — placeholder text shown in the text box.
 * - widthClass: string — Tailwind CSS width class for sizing the component.
 *
 * Data flow overview (high level):
 * 1) Parent passes props (like Flask passing context into a Jinja template).
 * 2) Inside this component, we keep local state (useState) for UI behavior and the chosen value.
 * 3) We fetch options from the backend (axios.get) using endpoint/field/q.
 * 4) When the user selects an option, we store it in state and also mirror its label in the text input.
 * 5) We render a hidden <input name={name} value={selected.id}> so the chosen id is submitted
 *    with the normal HTML form, analogous to how you'd send form data to a Flask view.
 */
export default function Typeahead({
  name,
  endpoint,
  field,
  initial = '',
  placeholder = 'Escriu per filtrar...',
  widthClass = 'w-64',
}) {
  // useState: like variables that remember their values between re-renders.
  // In Python terms, imagine a local variable that persists across function calls (React renders).
  const [open, setOpen] = useState(false);         // Is the dropdown menu open?
  const [query, setQuery] = useState(initial);     // Text inside the input (what the user typed)
  const [selected, setSelected] = useState(
    initial ? { id: String(initial), label: String(initial) } : null // The chosen option (id/label)
  );
  const [options, setOptions] = useState([]);      // Options returned by the server
  const [loading, setLoading] = useState(false);   // Are we currently fetching options?
  const [loaded, setLoaded] = useState(false);     // Prevent double initial loads

  // useRef: a mutable object that survives re-renders without causing re-render on change.
  // In Python, think of this like a tiny container with a .current value that you can mutate,
  // similar to a module-level variable used inside a function, but scoped to this component instance.
  const reqSeq = useRef(0);            // Sequence number to keep only the latest request's results
  const lastQuery = useRef(null);      // Last query sent: avoids repeating the same request
  const wrapRef = useRef(null);        // Ref to the outer wrapper; used to detect outside clicks
  const inputRef = useRef(null);       // Ref to the <input>; used to focus programmatically

  // fetchOptions: calls the backend to get options.
  // axios.get here is similar to Python's requests.get, e.g. requests.get(endpoint, params={...}).
  const fetchOptions = async (text) => {
    const current = ++reqSeq.current;  // Increment the sequence to mark this request as the latest
    setLoading(true);
    try {
      const { data } = await axios.get(endpoint, { params: { field, q: text ?? '' } });
      // If another request started after this one, ignore the older response
      if (current !== reqSeq.current) return;
      setOptions(data);
    } catch (e) {
      console.error(e);
    } finally {
      if (current === reqSeq.current) setLoading(false);
    }
  };

  // Event handler: input gets focus
  // Open the dropdown and, on first focus, fetch initial options once.
  const handleFocus = () => {
    setOpen(true);
    if (!loaded) {
      lastQuery.current = '';       // Force the first query to be sent only once
      fetchOptions('');
      setLoaded(true);
    }
  };

  // useEffect: run code after rendering.
  // Here we register a document click handler to close the menu on outside clicks.
  // In Flask terms, think of this like setting up and tearing down a little client-side hook
  // around the component's lifecycle (mount/unmount).
  useEffect(() => {
    const onDocClick = (e) => {
      if (!wrapRef.current) return;
      if (!wrapRef.current.contains(e.target)) setOpen(false);
    };
    document.addEventListener('mousedown', onDocClick);
    return () => document.removeEventListener('mousedown', onDocClick); // cleanup on unmount
  }, []);

  // useEffect with dependencies [query, open]: debounced searching.
  // If the menu is open and the user types, wait 250ms before sending a request.
  // We also skip sending if the query hasn't changed since the last call.
  useEffect(() => {
    if (!open) return;
    const id = setTimeout(() => {
      const q = query ?? '';
      if (q === lastQuery.current) return; // Don't repeat the same request
      lastQuery.current = q;
      fetchOptions(q);
    }, 250);
    return () => clearTimeout(id); // Cancel the pending call if query changes quickly
  }, [query, open]);

  // Event handler: user selects an option
  // Update selected state, mirror the label in the text box, and close the menu.
  const onSelect = (opt) => {
    setSelected(opt);
    setQuery(opt.label);
    setOpen(false);
  };

  // Keyboard interactions on the input
  // - Escape closes the menu
  // - ArrowDown opens (and triggers initial load if needed)
  // - Enter picks the first option when open
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

  // useMemo: cache a computed value unless dependencies change.
  // Here we just assemble a CSS class string and avoid recomputing it unless widthClass changes.
  const containerClass = useMemo(
    () => `inline-block ${widthClass} relative`, // relative: needed to anchor the dropdown menu
    [widthClass]
  );

  return (
    // wrapRef lets us detect clicks outside this wrapper
    <div className={containerClass} ref={wrapRef}>
      <div
        style={{ position: 'relative' }}
        // Clicking the field opens the menu, focuses the input, and triggers the first load if needed
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
        <input
          ref={inputRef}
          type="text"
          value={query}
          // onChange: fires on each keystroke; we update the query and ensure the menu is open
          onChange={(e) => { setQuery(e.target.value); setOpen(true); }}
          onFocus={handleFocus}
          onKeyDown={onKeyDown}
          placeholder={placeholder}
          className="block w-full rounded-md border border-gray-300 bg-white pl-3 pr-10 py-2 text-sm
                     focus:outline-none focus:border-indigo-500 placeholder-gray-400"
          role="combobox"
          aria-autocomplete="list"
          aria-expanded={open}
          autoComplete="off"
        />

        {/* Little chevron icon on the right */}
        <div style={{ position: 'absolute', right: '8px', left: 'auto', top: '50%', transform: 'translateY(-50%)', pointerEvents: 'none', color: '#6b7280' }}>
          <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" style={{ position: 'static', left: 'auto', display: 'block' }}>
            <path fillRule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.085l3.71-3.855a.75.75 0 111.08 1.04l-4.24 4.4a.75.75 0 01-1.08 0l-4.24-4.4a.75.75 0 01.02-1.06z" clipRule="evenodd" />
          </svg>
        </div>
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
              // onMouseDown instead of onClick to avoid the input losing focus before we handle selection
              onMouseDown={(e) => { e.preventDefault(); onSelect(opt); }}
              className="px-3 py-2 cursor-pointer text-sm hover:bg-gray-100"
              role="option"
            >
              {opt.label}
            </li>
          ))}
        </ul>
      )}

      {/* Hidden input: this is the value that will be submitted to the server when the form posts.
          It's like having a <input type="hidden" name="thing_id" value="123"> in a Flask/Jinja template. */}
      <input type="hidden" name={name} value={selected ? selected.id : ''} />
    </div>
  );
}
