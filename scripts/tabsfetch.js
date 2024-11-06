/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */

// Usamos un MutationObserver para detectar cuando se inserta un div con la clase .host-details
const observer = new MutationObserver(() => {
    const tabButtons = document.querySelectorAll(".host-details-tabs-head");

    tabButtons.forEach(button => {
        if (!button.hasAttribute("data-clicked")) {
            button.setAttribute("data-clicked", "true");

            button.addEventListener("click", async () => {
                const tabId = button.getAttribute("data-tab");
                console.log(tabId);
                try {
                    const response = await fetch("submitter.php", {
                        method: "POST",
                        headers: {"Content-Type": "application/json"},
                        body: JSON.stringify({command: "load_tab_data", tabId: tabId})
                    });

                    if (!response.ok)
                        throw new Error("Error en la solicitud");
                    const data = await response.json();
                } catch (error) {
                    console.error("Error al cargar los datos:", error);
                }
            });
        }
    });
});

// Iniciar observer sólo si aún no está observando
if (!observer.started) {
    observer.observe(document.body, {childList: true, subtree: true});
    observer.started = true; // Marca como iniciado
}
