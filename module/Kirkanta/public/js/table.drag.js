define(["$"], function($) {



  $.fn.tableDrag = function() {
    function findDropTarget(event) {
      for (var i = 0; i < event.target.parentElement.children.length; i++) {
        let row = event.target.parentElement.children[i];

        if (row != event.target) {
          let pos = event.target.offsetTop + event.offsetY - DRAG_ADJUST_POS;

          if (Math.abs(row.offsetTop - pos) < DROP_OFFSET_DELTA) {
            return row;
          }
        }
      }
    }

    /**
     * NOTE: This funcrag-active");
      $(DROP_TARGET_SUGGESTION).removeClass("drop-suggestion");
      let target = findDropTarget(event);

      if (target) {
        let affected_rows = getAffectedRows(event.target, target);
        let after = event.target.offsetTop < target.offsetTop;
        target.insertAdjacentElement(after ? "afterend" : "beforebegin", event.target);

        $(event.target).closest("table").trigger("tabledragsuccess", {
          rows: affected_rows,
        });
      }

      DROP_TARGET_SUGGESTION = null;tions needs to be called before executing the drop i.e. re-ordering elements.
     */
    function getAffectedRows(first, last) {
      if (last.offsetTop < first.offsetTop) {
        let tmp = first;
        first = last;
        last = tmp;
      }

      let affected = [first];

      do {
        first = first.nextElementSibling;
        affected.push(first);
      } while (first != last);

      return affected;
    }

    // Maximum distance from the drop target edge for accepting the drop.
    const DROP_OFFSET_DELTA = 20;

    // Distance from drag coordinate to dragged element's top edge.
    let DRAG_ADJUST_POS = 0;

    // Last accepted drop target element.
    let DROP_TARGET_SUGGESTION = null;
    let DRAG_ACTIVE_ELEMENT = null;

    this.find("tbody").attr("dropzone", "move").on("drop", function(event) {
      event.preventDefault();

      $(DRAG_ACTIVE_ELEMENT).removeClass("drag-active");
      $(DROP_TARGET_SUGGESTION).removeClass("drop-suggestion");

      if (DROP_TARGET_SUGGESTION) {
        let affected_rows = getAffectedRows(DRAG_ACTIVE_ELEMENT, DROP_TARGET_SUGGESTION);
        let after = DRAG_ACTIVE_ELEMENT.offsetTop < DROP_TARGET_SUGGESTION.offsetTop;
        DROP_TARGET_SUGGESTION.insertAdjacentElement(after ? "afterend" : "beforebegin", DRAG_ACTIVE_ELEMENT);

        $(DRAG_ACTIVE_ELEMENT).closest("table").trigger("tabledragsuccess", {
          rows: affected_rows,
        });
      }

      DROP_TARGET_SUGGESTION = null;
      DRAG_ACTIVE_ELEMENT = null;
    });

    this.find(".drag-handle").closest("tr")
      .prop("draggable", true)
      .on("mousedown", function(event) {
        if ($(event.target).closest(".drag-handle").length == 0) {
          event.preventDefault();
          // console.log(event.target);
        }
      })
      .on("dragstart", function(event) {
        DRAG_ADJUST_POS = event.offsetY;
        DRAG_ACTIVE_ELEMENT = event.target;


        // Firefox requires dummy data.
        try {
          event.originalEvent.dataTransfer.setData("text/html", "<b>firefox-fix</b>");
        } catch (e) {
          // pass
        }

        // IE requires dummy data with special MIME type.
        try {
          event.originalEvent.dataTransfer.setData("text", "ie-fix");
        } catch (e) {
          // pass
        }

        // Use timeout to allow the visual copy to retain original appearance.
        setTimeout(function() { $(DRAG_ACTIVE_ELEMENT).addClass("drag-active"); }, 100);
      })
      .on("dragover", function(event) {
        event.preventDefault();

        let target = event.currentTarget;

        if (target != DROP_TARGET_SUGGESTION) {
          $(target).addClass("drop-suggestion");
          $(DROP_TARGET_SUGGESTION).removeClass("drop-suggestion");

          DROP_TARGET_SUGGESTION = target;
        }
      })
      // .on("drag", (event) => {
      //   let target = findDropTarget(event);
      //
      //   console.log("T", target);
      //
      //   if (target != DROP_TARGET_SUGGESTION) {
      //     // console.log("T", target);
      //
      //     if (DROP_TARGET_SUGGESTION) {
      //       $(DROP_TARGET_SUGGESTION).removeClass("drop-suggestion");
      //     }
      //
      //     if (target) {
      //       $(target).addClass("drop-suggestion");
      //     }
      //
      //     DROP_TARGET_SUGGESTION = target;
      //   }
      // })
      // .on("dragend", (event) => {
      //   $(event.target).removeClass("drag-active");
      //   $(DROP_TARGET_SUGGESTION).removeClass("drop-suggestion");
      //   let target = findDropTarget(event);
      //
      //   if (target) {
      //     let affected_rows = getAffectedRows(event.target, target);
      //     let after = event.target.offsetTop < target.offsetTop;
      //     target.insertAdjacentElement(after ? "afterend" : "beforebegin", event.target);
      //
      //     $(event.target).closest("table").trigger("tabledragsuccess", {
      //       rows: affected_rows,
      //     });
      //   }
      //
      //   DROP_TARGET_SUGGESTION = null;
      // });

    return this;
  };


  $("table[data-app=\"table-drag\"]").tableDrag().on("tabledragsuccess", function(event, custom_data) {
    let url = window.location.pathname + "/tablesort";
    let handles = $(custom_data.rows).find(".drag-handle").toArray();

    $.post(url, {
      rows: handles.map(function(h) { return h.dataset.dragId }),
    });

  });



  return $;
});
