(() => {
  const conditionField = (conditioner, conditionees) => {
    conditioner = document.querySelector(conditioner);

    const show = (selector) => {
      document
        .querySelector(selector)
        .closest(".fields > div")
        .style.removeProperty("display");
    };
    const hide = (selector) => {
      document.querySelector(selector).closest(".fields > div").style.display =
        "none";
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
    conditionField("[name=document_type]", {
      5: ["[name=name]", "[name=last_name]"],
      2: ["[name=company_name]"],
    });

    conditionField("[name=payment_type]", {
      9: ["[name=payment_cbu]"],
      else: ["[name=payment_card_number]", "[name=payment_card_date]"],
    });
  });
})();
