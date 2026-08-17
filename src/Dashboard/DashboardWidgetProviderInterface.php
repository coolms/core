<?php

declare(strict_types=1);

namespace CoolMS\Core\Dashboard;

/**
 * Implemented by any module with something worth showing on a dashboard
 *.
 *
 * The same seam shape as the VFS file-kind provider
 * and for the same reason: the dashboard is assembled from whoever is
 * installed, so adding a module adds its widgets and removing one removes them,
 * with no edit here and none in the client. Core knows what a widget IS and
 * nothing about what any of them MEAN.
 *
 * Auto-tagged `coolms.core.dashboard_widget_provider` by the Core extension.
 * ⚠️ Modules registering services with `setAutoconfigured(false)` must add the
 * tag EXPLICITLY — implementing the interface is not enough there, which has
 * caught a contributor before.
 */
interface DashboardWidgetProviderInterface
{
    /**
     * The widgets this module offers, in the order it wants them shown.
     *
     * May be empty — a module whose figures depend on configuration, or that
     * has nothing to say until it has data, returns nothing rather than
     * offering a card that can only be blank.
     *
     * @return list<DashboardWidget>
     */
    public function dashboardWidgets(): array;
}
