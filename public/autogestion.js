(() => {
  const conditionField = (conditioner, conditionees) => {
    conditioner = document.querySelector(conditioner);
    if (!conditioner) return;

    const show = (selector) => {
      document
        .querySelector(selector)
        .closest(".fields > div")
        .style.removeProperty("display");
      document.querySelector(selector).removeAttribute("disabled");
    };
    const hide = (selector) => {
      document.querySelector(selector).closest(".fields > div").style.display =
        "none";
      document.querySelector(selector).setAttribute("disabled", true);
    };

    const conditionate = () => {
      const value = conditioner.value;
      const all = Object.values(conditionees).reduce((prev, curr) => {
        return prev.concat(curr);
      }, []);
      all.forEach(hide);
      let any = false;
      for (let [condition, conditionee] of Object.entries(conditionees)) {
        if (value == condition) {
          any = true;
          conditionee.forEach(show);
        }
      }
      if (!any && value != "" && conditionees.else) {
        conditionees.else.forEach(show);
      }
    };

    conditioner.addEventListener("change", conditionate);
    conditionate();
  };

  window.addEventListener("load", () => {
    conditionField("[name=id_tipo_documento]", {
      5: ["[name=nombre]", "[name=apellido]"],
      2: ["[name=razon_social]"],
    });

    conditionField("[name=id_medio_de_pago]", {
      9: ["[name=nro_cbu]"],
      else: ["[name=nro_tarjeta]", "[name=vencimiento_tarjeta]"],
    });
  });
})();
