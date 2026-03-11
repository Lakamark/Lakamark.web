← [Documentation](README.md) | [Architecture](architecture.md) | [Project Structure](project-structure.md)

---

## Foundation vs Infrastructure

The architecture distinguishes between **Foundation** and **Infrastructure**
to clearly separate reusable technical tools from external system integrations.

### Foundation

The **Foundation** layer contains reusable technical components that can be
used across different parts of the application.

These components are internal tools that support the application but are not
tied to a specific business domain.

Examples include:

- captcha systems
- mail builders
- security helpers
- shared utilities

Foundation acts as a toolbox providing technical building blocks that the
application can rely on.

---

### Infrastructure

The **Infrastructure** layer is responsible for connecting the application
to external systems and technical implementations.

Examples include:

- database persistence
- ORM implementations
- external APIs
- framework-specific adapters

Infrastructure isolates these technical concerns from the domain logic,
allowing the core application to remain independent from implementation
details.

---

### Summary

In simple terms:

* **Foundation** → reusable internal technical tools 
* **Infrastructure** → integrations with external systems
