export const frecuencias = [
  { value: 'semanal', label: 'Semanal' },
  { value: 'quincenal', label: 'Quincenal' },
  { value: 'mensual', label: 'Mensual' },
]

export const nombreFrecuencia = (valor) =>
  frecuencias.find((f) => f.value === valor)?.label ?? 'Sin frecuencia'