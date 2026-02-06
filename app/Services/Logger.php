<?php
declare(strict_types=1);

final class Logger
{
    public static function log(?int $userId, string $action, ?string $entity = null, ?int $entityId = null, ?string $details = null): void
    {
        $sql = "INSERT INTO logs (user_id, action, entity, entity_id, details)
                VALUES (:user_id, :action, :entity, :entity_id, :details)";

        $stmt = Database::pdo()->prepare($sql);
        $stmt->bindValue(':user_id', $userId, $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':action', $action);
        $stmt->bindValue(':entity', $entity);
        $stmt->bindValue(':entity_id', $entityId, $entityId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':details', $details);
        $stmt->execute();
    }
}
