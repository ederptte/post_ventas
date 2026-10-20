export const dias = [
  { value: 'lun', label: 'Lunes', short: 'Lun' },
  { value: 'mar', label: 'Martes', short: 'Mar' },
  { value: 'mie', label: 'Miércoles', short: 'Mié' },
  { value: 'jue', label: 'Jueves', short: 'Jue' },
  { value: 'vie', label: 'Viernes', short: 'Vie' },
  { value: 'sab', label: 'Sábado', short: 'Sáb' },
  { value: 'dom', label: 'Domingo', short: 'Dom' },
]

export const nombreDia = (valor) =>
  dias.find((d) => d.value === valor)?.label ?? valor

export const nombreCorto = (valor) =>
  dias.find((d) => d.value === valor)?.short ?? valor

// ["lun", "jue"] -> "Lunes y Jueves"
const lista = new Intl.ListFormat('es', { style: 'long', type: 'conjunction' })

export const formatearDias = (valor, corto = false) => {
  if (!Array.isArray(valor) || !valor.length) return 'Sin día fijo'
  return lista.format(valor.map((d) => (corto ? nombreCorto(d) : nombreDia(d))))
}