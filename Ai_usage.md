```markdown
# Registro de Uso de Inteligencia Artificial (AI_USAGE.md)

Para el desarrollo del asistente inteligente y componentes de este proyecto, se utilizaron herramientas de IA como apoyo a la programación.

## Herramientas Utilizadas
- **Modelos**: Gemini / OpenAI (gpt-4o-mini).
- **Tareas asistidas**: 
  - Estructuración de consultas complejas en Eloquent para el algoritmo FEFO (Primer Vencido, Primer Salido).
  - Generación de la arquitectura base del proveedor modular (`AIProviderInterface`) y el comando de evaluación automatizada (`php artisan assistant:evaluate`).
  - Apoyo en el tipado estricto de TypeScript para el frontend.

---

## Ejemplo de lo que se ACEPTÓ
- **Caso**: La implementación del patrón de diseño por contrato (`AIProviderInterface`).
- **Por qué se aceptó**: Permitió desacoplar totalmente la lógica del asistente de la API externa, logrando implementar un `MockAIProvider` determinista que pasa el 100 de las pruebas automatizadas del evaluador de IA sin requerir credenciales externas.

---

##  Ejemplo de lo que se RECHAZÓ o CORRIGIÓ
- **Caso**: Una propuesta inicial generada por la IA que sugería permitir al modelo de lenguaje interpretar consultas SQL libres directamente desde el prompt del usuario.
- **Por qué se rechazó**: Violaba gravemente la regla de seguridad de la prueba (riesgo de inyección SQL y exposición de datos médicos de pacientes). Se corrigió de inmediato para prohibir el acceso a SQL y restringir al modelo **únicamente a Function Calling de solo lectura** con funciones predefinidas y filtradas por roles.