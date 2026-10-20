export const entregas = [
  { value: 'mismo_dia', label: 'El mismo dia' },
  { value: 'dia_siguiente', label: 'Al dia siguiente' },
  { value: 'otro_dia', label: 'Otro dia' },
]

export const nombreEntrega = (valor) =>
  entregas.find((f) => f.value === valor)?.label ?? 'Sin entrega'